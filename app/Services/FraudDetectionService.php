<?php

namespace App\Services;

use App\Models\Alert;
use App\Models\Cdr;
use App\Models\Customer;
use App\Models\FraudIncident;
use App\Models\FraudRule;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class FraudDetectionService
{
    protected ?array $settings = null;

    /**
     * Analyze a call for fraud patterns
     */
    public function analyzeCall(Cdr $cdr): array
    {
        $incidents = [];

        if (!$this->isEnabled()) {
            return $incidents;
        }

        $customer = $cdr->customer;
        if (!$customer) {
            return $incidents;
        }

        // Get applicable rules
        $rules = FraudRule::active()
            ->forCustomer($customer->id)
            ->get();

        foreach ($rules as $rule) {
            if ($rule->isInCooldown()) {
                continue;
            }

            $detected = match($rule->type) {
                'high_cost_destination' => $this->checkHighCostDestination($cdr, $rule),
                'unusual_destination' => $this->checkUnusualDestination($cdr, $customer, $rule),
                'short_calls_burst' => $this->checkShortCallsBurst($cdr, $customer, $rule),
                default => null,
            };

            if ($detected) {
                $incident = $this->createIncident($rule, $customer, $cdr, $detected);
                $this->executeAction($incident, $rule);
                $incidents[] = $incident;
            }
        }

        return $incidents;
    }

    /**
     * Run periodic fraud analysis for all customers
     */
    public function runPeriodicAnalysis(): array
    {
        if (!$this->isEnabled()) {
            return [];
        }

        $incidents = [];
        $customers = Customer::where('active', true)->get();

        foreach ($customers as $customer) {
            $rules = FraudRule::active()
                ->forCustomer($customer->id)
                ->get();

            foreach ($rules as $rule) {
                if ($rule->isInCooldown()) {
                    continue;
                }

                $detected = match($rule->type) {
                    'traffic_spike' => $this->checkTrafficSpike($customer, $rule),
                    'wangiri' => $this->checkWangiriPattern($customer, $rule),
                    'high_failure_rate' => $this->checkHighFailureRate($customer, $rule),
                    'off_hours_traffic' => $this->checkOffHoursTraffic($customer, $rule),
                    'caller_id_manipulation' => $this->checkCallerIdManipulation($customer, $rule),
                    'accelerated_consumption' => $this->checkAcceleratedConsumption($customer, $rule),
                    'simultaneous_calls' => $this->checkSimultaneousCalls($customer, $rule),
                    default => null,
                };

                if ($detected) {
                    $incident = $this->createIncident($rule, $customer, null, $detected);
                    $this->executeAction($incident, $rule);
                    $incidents[] = $incident;
                }
            }
        }

        return $incidents;
    }

    /**
     * Check for high cost destination
     */
    protected function checkHighCostDestination(Cdr $cdr, FraudRule $rule): ?array
    {
        $premiumPrefixes = $this->getSetting('high_cost_prefixes', '');
        $prefixes = array_filter(array_map('trim', explode(',', $premiumPrefixes)));

        if (empty($prefixes)) {
            return null;
        }

        $callee = ltrim($cdr->callee, '+');
        foreach ($prefixes as $prefix) {
            if (str_starts_with($callee, $prefix)) {
                return [
                    'type' => 'high_cost_destination',
                    'title' => "Call to premium destination detected",
                    'description' => "Call to {$cdr->callee} matches premium prefix {$prefix}",
                    'metadata' => [
                        'callee' => $cdr->callee,
                        'prefix' => $prefix,
                        'duration' => $cdr->duration,
                        'cost' => $cdr->cost,
                    ],
                    'estimated_cost' => $cdr->cost,
                    'affected_calls' => 1,
                ];
            }
        }

        return null;
    }

    /**
     * Check for unusual destination
     */
    protected function checkUnusualDestination(Cdr $cdr, Customer $customer, FraudRule $rule): ?array
    {
        if (!$cdr->destination_prefix_id) {
            return null;
        }

        // Check if customer has ever called this prefix before
        $hasCalledBefore = Cdr::where('customer_id', $customer->id)
            ->where('destination_prefix_id', $cdr->destination_prefix_id)
            ->where('id', '!=', $cdr->id)
            ->exists();

        if (!$hasCalledBefore) {
            $prefix = $cdr->destinationPrefix;
            return [
                'type' => 'unusual_destination',
                'title' => "Call to new destination",
                'description' => "First call ever to {$prefix?->country_name} ({$prefix?->prefix})",
                'metadata' => [
                    'callee' => $cdr->callee,
                    'prefix' => $prefix?->prefix,
                    'country' => $prefix?->country_name,
                ],
                'estimated_cost' => $cdr->cost,
                'affected_calls' => 1,
            ];
        }

        return null;
    }

    /**
     * Check for traffic spike
     */
    protected function checkTrafficSpike(Customer $customer, FraudRule $rule): ?array
    {
        $threshold = $rule->threshold ?? $this->getSetting('traffic_spike_threshold', 200);
        $timeWindow = $rule->parameters['time_window_minutes'] ?? 60;

        // Current traffic
        $currentCalls = Cdr::where('customer_id', $customer->id)
            ->where('start_time', '>=', now()->subMinutes($timeWindow))
            ->count();

        // Historical average (same time, last 7 days)
        $historicalAvg = Cdr::where('customer_id', $customer->id)
            ->whereBetween('start_time', [
                now()->subDays(7)->subMinutes($timeWindow),
                now()->subDays(1),
            ])
            ->count() / 7;

        if ($historicalAvg > 0) {
            $percentageIncrease = (($currentCalls - $historicalAvg) / $historicalAvg) * 100;

            if ($percentageIncrease > $threshold) {
                return [
                    'type' => 'traffic_spike',
                    'title' => "Traffic spike detected",
                    'description' => sprintf(
                        "Traffic increased by %.0f%% (current: %d calls, avg: %.0f calls)",
                        $percentageIncrease,
                        $currentCalls,
                        $historicalAvg
                    ),
                    'metadata' => [
                        'current_calls' => $currentCalls,
                        'historical_avg' => $historicalAvg,
                        'percentage_increase' => $percentageIncrease,
                        'time_window_minutes' => $timeWindow,
                    ],
                    'affected_calls' => $currentCalls,
                ];
            }
        }

        return null;
    }

    /**
     * Check for Wangiri (short calls) pattern
     */
    protected function checkWangiriPattern(Customer $customer, FraudRule $rule): ?array
    {
        $threshold = $rule->threshold ?? $this->getSetting('short_call_threshold', 50);
        $maxDuration = $rule->parameters['max_duration_seconds'] ?? 6;
        $timeWindow = $rule->parameters['time_window_minutes'] ?? 5;

        $shortCalls = Cdr::where('customer_id', $customer->id)
            ->where('start_time', '>=', now()->subMinutes($timeWindow))
            ->where('sip_code', 200)
            ->where('duration', '<', $maxDuration)
            ->count();

        if ($shortCalls >= $threshold) {
            return [
                'type' => 'wangiri',
                'title' => "Wangiri pattern detected",
                'description' => sprintf(
                    "%d short calls (<%ds) in last %d minutes",
                    $shortCalls,
                    $maxDuration,
                    $timeWindow
                ),
                'metadata' => [
                    'short_calls' => $shortCalls,
                    'max_duration' => $maxDuration,
                    'time_window' => $timeWindow,
                ],
                'affected_calls' => $shortCalls,
            ];
        }

        return null;
    }

    /**
     * Check for high failure rate
     */
    protected function checkHighFailureRate(Customer $customer, FraudRule $rule): ?array
    {
        $threshold = $rule->threshold ?? $this->getSetting('failure_rate_threshold', 80);
        $timeWindow = $rule->parameters['time_window_minutes'] ?? 30;
        $minCalls = $rule->parameters['min_calls'] ?? 20;

        $stats = Cdr::where('customer_id', $customer->id)
            ->where('start_time', '>=', now()->subMinutes($timeWindow))
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN sip_code >= 400 THEN 1 ELSE 0 END) as failed
            ')
            ->first();

        if ($stats->total >= $minCalls && $stats->total > 0) {
            $failureRate = ($stats->failed / $stats->total) * 100;

            if ($failureRate >= $threshold) {
                return [
                    'type' => 'high_failure_rate',
                    'title' => "High failure rate detected",
                    'description' => sprintf(
                        "%.0f%% failure rate (%d/%d calls) in last %d minutes",
                        $failureRate,
                        $stats->failed,
                        $stats->total,
                        $timeWindow
                    ),
                    'metadata' => [
                        'failure_rate' => $failureRate,
                        'failed_calls' => $stats->failed,
                        'total_calls' => $stats->total,
                        'time_window' => $timeWindow,
                    ],
                    'affected_calls' => $stats->failed,
                ];
            }
        }

        return null;
    }

    /**
     * Check for off-hours traffic
     */
    protected function checkOffHoursTraffic(Customer $customer, FraudRule $rule): ?array
    {
        $startTime = $this->getSetting('off_hours_start', '22:00');
        $endTime = $this->getSetting('off_hours_end', '06:00');
        $threshold = $rule->threshold ?? 10;

        $now = now();
        $isOffHours = false;

        // Check if current time is in off-hours
        $start = $now->copy()->setTimeFromTimeString($startTime);
        $end = $now->copy()->setTimeFromTimeString($endTime);

        if ($start > $end) {
            // Spans midnight
            $isOffHours = $now >= $start || $now <= $end;
        } else {
            $isOffHours = $now >= $start && $now <= $end;
        }

        if (!$isOffHours) {
            return null;
        }

        // Count calls in last hour during off-hours
        $calls = Cdr::where('customer_id', $customer->id)
            ->where('start_time', '>=', now()->subHour())
            ->count();

        if ($calls >= $threshold) {
            return [
                'type' => 'off_hours_traffic',
                'title' => "Off-hours traffic detected",
                'description' => sprintf(
                    "%d calls detected during off-hours (%s - %s)",
                    $calls,
                    $startTime,
                    $endTime
                ),
                'metadata' => [
                    'calls' => $calls,
                    'off_hours_start' => $startTime,
                    'off_hours_end' => $endTime,
                ],
                'affected_calls' => $calls,
            ];
        }

        return null;
    }

    /**
     * Check for caller ID manipulation
     */
    protected function checkCallerIdManipulation(Customer $customer, FraudRule $rule): ?array
    {
        $threshold = $rule->threshold ?? $this->getSetting('caller_id_changes_threshold', 10);
        $timeWindow = $rule->parameters['time_window_minutes'] ?? 60;

        $uniqueCallerIds = Cdr::where('customer_id', $customer->id)
            ->where('start_time', '>=', now()->subMinutes($timeWindow))
            ->distinct('caller')
            ->count('caller');

        if ($uniqueCallerIds >= $threshold) {
            return [
                'type' => 'caller_id_manipulation',
                'title' => "Multiple caller IDs detected",
                'description' => sprintf(
                    "%d different caller IDs used in last %d minutes",
                    $uniqueCallerIds,
                    $timeWindow
                ),
                'metadata' => [
                    'unique_caller_ids' => $uniqueCallerIds,
                    'time_window' => $timeWindow,
                ],
            ];
        }

        return null;
    }

    /**
     * Check for accelerated consumption
     */
    protected function checkAcceleratedConsumption(Customer $customer, FraudRule $rule): ?array
    {
        $threshold = $rule->threshold ?? $this->getSetting('minutes_acceleration_threshold', 300);

        // Current day usage rate
        $hoursToday = max(1, now()->diffInHours(now()->startOfDay()));
        $todayMinutes = Cdr::where('customer_id', $customer->id)
            ->whereDate('start_time', today())
            ->where('sip_code', 200)
            ->sum('billable_duration') / 60;
        $todayRate = $todayMinutes / $hoursToday;

        // Average rate over last 7 days
        $weekMinutes = Cdr::where('customer_id', $customer->id)
            ->whereBetween('start_time', [now()->subDays(7), now()->subDay()])
            ->where('sip_code', 200)
            ->sum('billable_duration') / 60;
        $avgRate = $weekMinutes / (7 * 24);

        if ($avgRate > 0) {
            $acceleration = (($todayRate - $avgRate) / $avgRate) * 100;

            if ($acceleration >= $threshold) {
                return [
                    'type' => 'accelerated_consumption',
                    'title' => "Accelerated usage detected",
                    'description' => sprintf(
                        "Usage rate %.0f%% higher than average (%.1f min/h vs %.1f min/h)",
                        $acceleration,
                        $todayRate,
                        $avgRate
                    ),
                    'metadata' => [
                        'today_rate' => $todayRate,
                        'average_rate' => $avgRate,
                        'acceleration_percent' => $acceleration,
                    ],
                ];
            }
        }

        return null;
    }

    /**
     * Check for too many simultaneous calls
     */
    protected function checkSimultaneousCalls(Customer $customer, FraudRule $rule): ?array
    {
        $threshold = $rule->threshold ?? ($customer->max_channels * 0.9); // 90% of max

        $activeCalls = Redis::get("customer:{$customer->id}:channels") ?? 0;

        if ($activeCalls >= $threshold) {
            return [
                'type' => 'simultaneous_calls',
                'title' => "High concurrent call volume",
                'description' => sprintf(
                    "%d simultaneous calls (limit: %d)",
                    $activeCalls,
                    $customer->max_channels
                ),
                'metadata' => [
                    'active_calls' => $activeCalls,
                    'max_channels' => $customer->max_channels,
                ],
                'affected_calls' => $activeCalls,
            ];
        }

        return null;
    }

    /**
     * Short calls burst detection
     */
    protected function checkShortCallsBurst(Cdr $cdr, Customer $customer, FraudRule $rule): ?array
    {
        // Only check if this was a short call
        if ($cdr->duration >= 6) {
            return null;
        }

        $threshold = $rule->threshold ?? 10;
        $timeWindow = $rule->parameters['time_window_minutes'] ?? 5;

        $shortCalls = Cdr::where('customer_id', $customer->id)
            ->where('start_time', '>=', now()->subMinutes($timeWindow))
            ->where('sip_code', 200)
            ->where('duration', '<', 6)
            ->count();

        if ($shortCalls >= $threshold) {
            return [
                'type' => 'short_calls_burst',
                'title' => "Short calls burst detected",
                'description' => sprintf(
                    "%d short calls (<6s) in last %d minutes",
                    $shortCalls,
                    $timeWindow
                ),
                'metadata' => [
                    'short_calls' => $shortCalls,
                    'time_window' => $timeWindow,
                ],
                'affected_calls' => $shortCalls,
            ];
        }

        return null;
    }

    /**
     * Create fraud incident
     */
    public function createIncident(FraudRule $rule, Customer $customer, ?Cdr $cdr, array $data): FraudIncident
    {
        return FraudIncident::create([
            'fraud_rule_id' => $rule->id,
            'customer_id' => $customer->id,
            'cdr_id' => $cdr?->id,
            'type' => $data['type'],
            'severity' => $rule->severity,
            'title' => $data['title'],
            'description' => $data['description'],
            'metadata' => $data['metadata'] ?? null,
            'estimated_cost' => $data['estimated_cost'] ?? null,
            'affected_calls' => $data['affected_calls'] ?? null,
            'status' => 'pending',
            'action_taken' => 'none',
        ]);
    }

    /**
     * Execute action for fraud incident
     */
    public function executeAction(FraudIncident $incident, FraudRule $rule): void
    {
        $rule->markTriggered();

        switch ($rule->action) {
            case 'alert':
                $this->sendAlert($incident);
                $incident->update(['action_taken' => 'notified']);
                break;

            case 'throttle':
                $this->throttleCustomer($incident->customer);
                $this->sendAlert($incident);
                $incident->update(['action_taken' => 'throttled']);
                break;

            case 'block':
                if ($this->isAutoBlockEnabled()) {
                    $this->blockCustomer($incident->customer);
                    $incident->update(['action_taken' => 'blocked']);
                }
                $this->sendAlert($incident);
                break;

            case 'log':
            default:
                // Just log, no action
                break;
        }
    }

    /**
     * Send alert for fraud incident
     */
    protected function sendAlert(FraudIncident $incident): void
    {
        Alert::create([
            'type' => 'fraud_detected',
            'severity' => $incident->severity === 'critical' ? 'critical' : 'warning',
            'source_type' => 'customer',
            'source_id' => $incident->customer_id,
            'source_name' => $incident->customer?->name,
            'title' => $incident->title,
            'message' => $incident->description,
            'metadata' => [
                'incident_id' => $incident->id,
                'fraud_type' => $incident->type,
                'metadata' => $incident->metadata,
            ],
        ]);

        $incident->update(['notified_admin' => true]);

        Log::warning("Fraud detected", [
            'incident_id' => $incident->id,
            'customer_id' => $incident->customer_id,
            'type' => $incident->type,
        ]);
    }

    /**
     * Throttle customer traffic
     */
    protected function throttleCustomer(Customer $customer): void
    {
        // Reduce CPS temporarily
        $newCps = max(1, (int) ($customer->max_cps * 0.5));
        Redis::setex("customer:{$customer->id}:throttled_cps", 3600, $newCps);

        Log::info("Customer throttled", [
            'customer_id' => $customer->id,
            'original_cps' => $customer->max_cps,
            'throttled_cps' => $newCps,
        ]);
    }

    /**
     * Block customer
     */
    protected function blockCustomer(Customer $customer): void
    {
        Redis::setex("customer:{$customer->id}:blocked", 3600, 1);

        Log::warning("Customer blocked due to fraud", [
            'customer_id' => $customer->id,
        ]);
    }

    /**
     * Calculate risk score for a customer
     */
    public function calculateRiskScore(Customer $customer): int
    {
        $score = 0;

        // Recent incidents
        $recentIncidents = FraudIncident::where('customer_id', $customer->id)
            ->where('created_at', '>=', now()->subDays(30))
            ->whereNotIn('status', ['false_positive'])
            ->get();

        foreach ($recentIncidents as $incident) {
            $score += match($incident->severity) {
                'low' => 5,
                'medium' => 15,
                'high' => 30,
                'critical' => 50,
                default => 10,
            };
        }

        // Cap at 100
        return min(100, $score);
    }

    /**
     * Calculate risk scores for all active customers
     */
    public function calculateAllRiskScores(): array
    {
        $customers = Customer::where('active', true)->get();
        $scores = [];

        foreach ($customers as $customer) {
            $score = $this->calculateRiskScore($customer);
            $incidentsCount = FraudIncident::where('customer_id', $customer->id)
                ->where('created_at', '>=', now()->subDays(30))
                ->count();

            $scores[] = [
                'customer' => $customer,
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'score' => $score,
                'level' => $this->getRiskLevel($score),
                'incidents_count' => $incidentsCount,
            ];
        }

        return $scores;
    }

    /**
     * Get risk level label from score
     */
    protected function getRiskLevel(int $score): string
    {
        if ($score >= 70) return 'critical';
        if ($score >= 50) return 'high';
        if ($score >= 25) return 'medium';
        if ($score >= 10) return 'low';
        return 'none';
    }

    /**
     * Get fraud statistics
     */
    public function getStats(string $from, string $to): array
    {
        return [
            'total_incidents' => FraudIncident::whereBetween('created_at', [$from, $to])->count(),
            'by_type' => FraudIncident::whereBetween('created_at', [$from, $to])
                ->selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type')
                ->toArray(),
            'by_severity' => FraudIncident::whereBetween('created_at', [$from, $to])
                ->selectRaw('severity, COUNT(*) as count')
                ->groupBy('severity')
                ->pluck('count', 'severity')
                ->toArray(),
            'by_status' => FraudIncident::whereBetween('created_at', [$from, $to])
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray(),
            'pending_count' => FraudIncident::pending()->count(),
        ];
    }

    /**
     * Check if fraud detection is enabled
     */
    protected function isEnabled(): bool
    {
        return (bool) $this->getSetting('detection_enabled', true);
    }

    /**
     * Check if auto-block is enabled
     */
    protected function isAutoBlockEnabled(): bool
    {
        return (bool) $this->getSetting('auto_block_enabled', false);
    }

    /**
     * Get setting value
     */
    protected function getSetting(string $name, $default = null)
    {
        if ($this->settings === null) {
            $this->settings = SystemSetting::where('category', 'fraud')
                ->pluck('value', 'name')
                ->toArray();
        }

        return $this->settings[$name] ?? $default;
    }
}
