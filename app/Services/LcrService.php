<?php

namespace App\Services;

use App\Models\Carrier;
use App\Models\CarrierRate;
use App\Models\Customer;
use App\Models\CustomerRate;
use App\Models\DestinationPrefix;
use App\Models\Cdr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class LcrService
{
    protected NumberNormalizationService $normalizationService;

    public function __construct(NumberNormalizationService $normalizationService)
    {
        $this->normalizationService = $normalizationService;
    }

    protected array $codecQuality = [
        'PCMU' => 4.5,    // G.711 u-law
        'PCMA' => 4.5,    // G.711 a-law
        'G722' => 4.0,    // G.722
        'G729' => 3.92,   // G.729
        'GSM' => 3.5,     // GSM
        'OPUS' => 4.5,    // Opus
    ];

    /**
     * Select the best carrier for a call based on LCR
     *
     * This method first normalizes the called number according to the customer's
     * number format settings, then performs the LCR lookup.
     */
    public function selectCarrier(string $calledNumber, Customer $customer, array $options = []): ?array
    {
        // Normalize the number according to customer's settings
        $normalization = $this->normalizationService->normalize($calledNumber, $customer);
        $normalizedNumber = $normalization['normalized'];
        $originalNumber = $calledNumber;

        $prefix = $this->findDestinationPrefix($normalizedNumber);

        // Check customer's dialing plan restrictions using normalized number
        $dialingCheck = $this->checkDialingPlan($customer, $normalizedNumber, $prefix);
        if (!$dialingCheck['allowed']) {
            return [
                'error' => true,
                'code' => 403,
                'reason' => $dialingCheck['reason'],
                'message' => $dialingCheck['message'],
                'original_number' => $originalNumber,
                'normalized_number' => $normalizedNumber,
                'normalization' => $normalization,
            ];
        }

        if (!$prefix) {
            $result = $this->fallbackToPriority($normalizedNumber, $options);
            if ($result) {
                $result['original_number'] = $originalNumber;
                $result['normalized_number'] = $normalizedNumber;
                $result['normalization'] = $normalization;
            }
            return $result;
        }

        $carrierRates = $this->getCarrierRatesForDestination($prefix, $options);
        if ($carrierRates->isEmpty()) {
            $result = $this->fallbackToPriority($normalizedNumber, $options);
            if ($result) {
                $result['original_number'] = $originalNumber;
                $result['normalized_number'] = $normalizedNumber;
                $result['normalization'] = $normalization;
            }
            return $result;
        }

        // Filter by carrier availability (channels, CPS)
        foreach ($carrierRates as $rate) {
            $carrier = $rate->carrier;
            if ($this->isCarrierAvailable($carrier)) {
                return [
                    'carrier' => $carrier,
                    'rate' => $rate,
                    'prefix' => $prefix,
                    'cost_per_minute' => $rate->cost_per_minute,
                    'original_number' => $originalNumber,
                    'normalized_number' => $normalizedNumber,
                    'normalization' => $normalization,
                ];
            }
        }

        // All carriers busy, try fallback
        $result = $this->fallbackToPriority($normalizedNumber, $options);
        if ($result) {
            $result['original_number'] = $originalNumber;
            $result['normalized_number'] = $normalizedNumber;
            $result['normalization'] = $normalization;
        }
        return $result;
    }

    /**
     * Check if customer is allowed to dial based on their dialing plan
     */
    public function checkDialingPlan(Customer $customer, string $number, ?DestinationPrefix $prefix = null): array
    {
        return $customer->canDialNumber($number, $prefix);
    }

    /**
     * Find the longest matching destination prefix
     */
    public function findDestinationPrefix(string $number): ?DestinationPrefix
    {
        // Remove any + prefix
        $number = ltrim($number, '+');

        // Try to find from cache first
        $cacheKey = "prefix:{$number}";
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached === 'none' ? null : DestinationPrefix::find($cached);
        }

        // Find longest matching prefix
        $prefix = null;
        $maxLength = 0;

        // Get all active prefixes and find the longest match
        $prefixes = DestinationPrefix::active()
            ->orderByRaw('LENGTH(prefix) DESC')
            ->get();

        foreach ($prefixes as $p) {
            if (str_starts_with($number, $p->prefix)) {
                if (strlen($p->prefix) > $maxLength) {
                    $prefix = $p;
                    $maxLength = strlen($p->prefix);
                    break; // Already sorted by length DESC
                }
            }
        }

        // Cache the result
        Cache::put($cacheKey, $prefix ? $prefix->id : 'none', 3600);

        return $prefix;
    }

    /**
     * Get carrier rates for a destination, ordered by cost
     */
    public function getCarrierRatesForDestination(DestinationPrefix $prefix, array $options = [])
    {
        $date = $options['date'] ?? now()->toDateString();

        return CarrierRate::with('carrier')
            ->where('destination_prefix_id', $prefix->id)
            ->active()
            ->effective($date)
            ->whereHas('carrier', function ($q) {
                $q->where('state', 'active');
            })
            ->orderBy('cost_per_minute', 'asc')
            ->get();
    }

    /**
     * Calculate cost for a CDR
     */
    public function calculateCdrCost(Cdr $cdr): ?float
    {
        if (!$cdr->carrier_id || $cdr->billable_duration <= 0) {
            return null;
        }

        $prefix = $cdr->destination_prefix_id
            ? DestinationPrefix::find($cdr->destination_prefix_id)
            : $this->findDestinationPrefix($cdr->callee);

        if (!$prefix) {
            return null;
        }

        $rate = CarrierRate::where('carrier_id', $cdr->carrier_id)
            ->where('destination_prefix_id', $prefix->id)
            ->active()
            ->effective($cdr->start_time->toDateString())
            ->first();

        if (!$rate) {
            return null;
        }

        return $rate->calculateCost($cdr->billable_duration);
    }

    /**
     * Calculate price for a CDR based on customer rates
     */
    public function calculateCdrPrice(Cdr $cdr): ?float
    {
        if (!$cdr->customer_id || $cdr->billable_duration <= 0) {
            return null;
        }

        $prefix = $cdr->destination_prefix_id
            ? DestinationPrefix::find($cdr->destination_prefix_id)
            : $this->findDestinationPrefix($cdr->callee);

        if (!$prefix) {
            return null;
        }

        $customer = $cdr->customer;
        $date = $cdr->start_time->toDateString();

        // Priority 1: Customer-specific rate
        $customerRate = CustomerRate::where('customer_id', $customer->id)
            ->where('destination_prefix_id', $prefix->id)
            ->active()
            ->effective($date)
            ->first();

        if ($customerRate) {
            return $customerRate->calculatePrice($cdr->billable_duration);
        }

        // Priority 2: Rate plan rate
        if ($customer->rate_plan_id) {
            $ratePlan = $customer->ratePlan;
            $planRate = $ratePlan->getRateForDestination($prefix->id, $date);

            if ($planRate) {
                return $planRate->calculatePrice($cdr->billable_duration);
            }

            // Use default markup if no specific rate in plan
            $cost = $this->calculateCdrCost($cdr);
            if ($cost !== null) {
                return $ratePlan->calculatePriceFromCost($cost);
            }
        }

        return null;
    }

    /**
     * Calculate billing for a CDR (cost, price, profit, margin)
     */
    public function calculateCdrBilling(Cdr $cdr): array
    {
        $cost = $this->calculateCdrCost($cdr) ?? 0;
        $price = $this->calculateCdrPrice($cdr) ?? 0;
        $profit = round($price - $cost, 6);
        $margin = $price > 0 ? round(($profit / $price) * 100, 4) : 0;

        // Find and set destination prefix if not set
        $prefixId = $cdr->destination_prefix_id;
        if (!$prefixId) {
            $prefix = $this->findDestinationPrefix($cdr->callee);
            $prefixId = $prefix?->id;
        }

        return [
            'destination_prefix_id' => $prefixId,
            'cost' => $cost,
            'price' => $price,
            'profit' => $profit,
            'margin_percent' => $margin,
        ];
    }

    /**
     * Sync LCR data to Redis for Kamailio
     */
    public function syncToRedis(): int
    {
        $count = 0;
        $date = now()->toDateString();

        // Clear existing LCR data
        $keys = Redis::keys('lcr:*');
        if (!empty($keys)) {
            Redis::del($keys);
        }

        // Get all active carrier rates
        $rates = CarrierRate::with(['carrier', 'destinationPrefix'])
            ->active()
            ->effective($date)
            ->whereHas('carrier', function ($q) {
                $q->where('state', 'active');
            })
            ->orderBy('destination_prefix_id')
            ->orderBy('cost_per_minute')
            ->get();

        // Group by prefix
        $byPrefix = $rates->groupBy('destination_prefix_id');

        foreach ($byPrefix as $prefixId => $prefixRates) {
            $prefix = $prefixRates->first()->destinationPrefix;
            $carriers = [];

            foreach ($prefixRates as $rate) {
                $carrier = $rate->carrier;
                $carriers[] = [
                    'id' => $carrier->id,
                    'host' => $carrier->host,
                    'port' => $carrier->port,
                    'cost' => (float) $rate->cost_per_minute,
                    'tech_prefix' => $carrier->tech_prefix,
                    'strip_digits' => $carrier->strip_digits,
                ];
            }

            // Store in Redis: lcr:<prefix> = JSON array of carriers sorted by cost
            Redis::set("lcr:{$prefix->prefix}", json_encode($carriers));
            $count++;
        }

        // Store prefix list for quick lookup
        $prefixes = $byPrefix->keys()->map(function ($prefixId) use ($byPrefix) {
            return $byPrefix[$prefixId]->first()->destinationPrefix->prefix;
        })->toArray();

        // Sort by length DESC for longest prefix matching
        usort($prefixes, function ($a, $b) {
            return strlen($b) - strlen($a);
        });

        Redis::set('lcr:prefixes', json_encode($prefixes));
        Redis::set('lcr:last_sync', now()->toISOString());

        return $count;
    }

    /**
     * Check if carrier is available (not over capacity)
     */
    protected function isCarrierAvailable(Carrier $carrier): bool
    {
        // Check if carrier is active
        if ($carrier->state !== 'active') {
            return false;
        }

        // Check current channels
        $currentChannels = Redis::get("carrier:{$carrier->id}:channels") ?? 0;
        if ($currentChannels >= $carrier->max_channels) {
            return false;
        }

        // Check CPS
        $currentCps = Redis::get("carrier:{$carrier->id}:cps") ?? 0;
        if ($currentCps >= $carrier->max_cps) {
            return false;
        }

        return true;
    }

    /**
     * Fallback to priority-based routing
     */
    protected function fallbackToPriority(string $calledNumber, array $options = []): ?array
    {
        $setting = DB::table('system_settings')
            ->where('category', 'lcr')
            ->where('name', 'fallback_to_priority')
            ->value('value');

        if ($setting !== '1') {
            return null;
        }

        $carrier = Carrier::where('state', 'active')
            ->orderBy('priority', 'asc')
            ->orderBy('weight', 'desc')
            ->first();

        if (!$carrier || !$this->isCarrierAvailable($carrier)) {
            return null;
        }

        return [
            'carrier' => $carrier,
            'rate' => null,
            'prefix' => null,
            'cost_per_minute' => null,
        ];
    }

    /**
     * Get LCR lookup result for API/debugging
     */
    public function lcrLookup(string $number, ?int $customerId = null): array
    {
        $customer = $customerId ? Customer::find($customerId) : null;

        // Normalize number if customer is specified
        $normalization = null;
        $normalizedNumber = $number;
        if ($customer) {
            $normalization = $this->normalizationService->normalize($number, $customer);
            $normalizedNumber = $normalization['normalized'];
        }

        $prefix = $this->findDestinationPrefix($normalizedNumber);
        $result = [
            'number' => $number,
            'normalized_number' => $normalizedNumber,
            'normalization' => $normalization,
            'prefix' => $prefix ? [
                'id' => $prefix->id,
                'prefix' => $prefix->prefix,
                'country' => $prefix->country_name,
                'region' => $prefix->region,
                'is_premium' => $prefix->is_premium,
            ] : null,
            'carriers' => [],
            'customer_rate' => null,
        ];

        if ($prefix) {
            $carrierRates = $this->getCarrierRatesForDestination($prefix);
            foreach ($carrierRates as $rate) {
                $result['carriers'][] = [
                    'carrier_id' => $rate->carrier_id,
                    'carrier_name' => $rate->carrier->name,
                    'cost_per_minute' => (float) $rate->cost_per_minute,
                    'connection_fee' => (float) $rate->connection_fee,
                    'billing_increment' => $rate->billing_increment,
                    'available' => $this->isCarrierAvailable($rate->carrier),
                ];
            }

            if ($customerId) {
                $customer = Customer::find($customerId);
                if ($customer) {
                    $customerRate = CustomerRate::where('customer_id', $customerId)
                        ->where('destination_prefix_id', $prefix->id)
                        ->active()
                        ->effective()
                        ->first();

                    if ($customerRate) {
                        $result['customer_rate'] = [
                            'source' => 'customer_rate',
                            'price_per_minute' => (float) $customerRate->price_per_minute,
                            'connection_fee' => (float) $customerRate->connection_fee,
                        ];
                    } elseif ($customer->rate_plan_id) {
                        $planRate = $customer->ratePlan->getRateForDestination($prefix->id);
                        if ($planRate) {
                            $result['customer_rate'] = [
                                'source' => 'rate_plan',
                                'rate_plan' => $customer->ratePlan->name,
                                'price_per_minute' => (float) $planRate->price_per_minute,
                                'connection_fee' => (float) $planRate->connection_fee,
                            ];
                        }
                    }
                }
            }
        }

        return $result;
    }
}
