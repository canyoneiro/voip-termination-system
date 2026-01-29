<?php

namespace App\Http\Controllers\Api;

use App\Models\CarrierRate;
use App\Models\CustomerRate;
use App\Models\DestinationPrefix;
use App\Models\RatePlan;
use App\Services\LcrService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RateController extends BaseApiController
{
    public function __construct(
        protected LcrService $lcrService
    ) {}

    /**
     * LCR lookup for a number
     */
    public function lcrLookup(Request $request): JsonResponse
    {
        $request->validate([
            'number' => 'required|string|min:3|max:30',
            'customer_id' => 'nullable|integer|exists:customers,id',
        ]);

        $result = $this->lcrService->lcrLookup(
            $request->input('number'),
            $request->input('customer_id')
        );

        return $this->success($result);
    }

    /**
     * List destination prefixes
     */
    public function destinations(Request $request): JsonResponse
    {
        $query = DestinationPrefix::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('prefix', 'like', "{$search}%")
                    ->orWhere('country_name', 'like', "%{$search}%")
                    ->orWhere('region', 'like', "%{$search}%");
            });
        }

        if ($request->filled('country_code')) {
            $query->where('country_code', $request->input('country_code'));
        }

        if ($request->boolean('premium')) {
            $query->where('is_premium', true);
        }

        if ($request->boolean('active', true)) {
            $query->where('active', true);
        }

        $perPage = min($request->input('per_page', 50), 200);
        $prefixes = $query->orderBy('prefix')->paginate($perPage);

        return $this->paginated($prefixes);
    }

    /**
     * Get single destination prefix
     */
    public function showDestination(DestinationPrefix $prefix): JsonResponse
    {
        return $this->success($prefix);
    }

    /**
     * Create destination prefix
     */
    public function storeDestination(Request $request): JsonResponse
    {
        $data = $request->validate([
            'prefix' => 'required|string|max:20|unique:destination_prefixes,prefix',
            'country_code' => 'nullable|string|max:3',
            'country_name' => 'nullable|string|max:100',
            'region' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:255',
            'is_mobile' => 'boolean',
            'is_premium' => 'boolean',
            'active' => 'boolean',
        ]);

        $prefix = DestinationPrefix::create($data);

        return $this->success($prefix, [], 201);
    }

    /**
     * Update destination prefix
     */
    public function updateDestination(Request $request, DestinationPrefix $prefix): JsonResponse
    {
        $data = $request->validate([
            'prefix' => "string|max:20|unique:destination_prefixes,prefix,{$prefix->id}",
            'country_code' => 'nullable|string|max:3',
            'country_name' => 'nullable|string|max:100',
            'region' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:255',
            'is_mobile' => 'boolean',
            'is_premium' => 'boolean',
            'active' => 'boolean',
        ]);

        $prefix->update($data);

        return $this->success($prefix);
    }

    /**
     * List carrier rates
     */
    public function carrierRates(Request $request): JsonResponse
    {
        $query = CarrierRate::with(['carrier', 'destinationPrefix']);

        if ($request->filled('carrier_id')) {
            $query->where('carrier_id', $request->input('carrier_id'));
        }

        if ($request->filled('prefix_id')) {
            $query->where('destination_prefix_id', $request->input('prefix_id'));
        }

        if ($request->boolean('active', true)) {
            $query->active()->effective();
        }

        $perPage = min($request->input('per_page', 50), 200);
        $rates = $query->orderBy('cost_per_minute')->paginate($perPage);

        return $this->paginated($rates);
    }

    /**
     * Create carrier rate
     */
    public function storeCarrierRate(Request $request): JsonResponse
    {
        $data = $request->validate([
            'carrier_id' => 'required|exists:carriers,id',
            'destination_prefix_id' => 'required|exists:destination_prefixes,id',
            'cost_per_minute' => 'required|numeric|min:0',
            'connection_fee' => 'numeric|min:0',
            'billing_increment' => 'integer|min:1|max:60',
            'min_duration' => 'integer|min:0|max:300',
            'effective_date' => 'required|date',
            'end_date' => 'nullable|date|after:effective_date',
        ]);

        $rate = CarrierRate::create($data);

        return $this->success($rate->load(['carrier', 'destinationPrefix']), [], 201);
    }

    /**
     * List rate plans
     */
    public function ratePlans(Request $request): JsonResponse
    {
        $query = RatePlan::withCount('rates', 'customers');

        if ($request->boolean('active', true)) {
            $query->active();
        }

        $plans = $query->orderBy('name')->get();

        return $this->success($plans);
    }

    /**
     * Get rate plan with rates
     */
    public function showRatePlan(RatePlan $ratePlan): JsonResponse
    {
        $ratePlan->load(['rates.destinationPrefix']);

        return $this->success($ratePlan);
    }

    /**
     * Create rate plan
     */
    public function storeRatePlan(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'default_markup_percent' => 'numeric|min:0|max:1000',
            'default_markup_fixed' => 'numeric|min:0',
            'billing_increment' => 'integer|min:1|max:60',
            'min_duration' => 'integer|min:0|max:300',
            'active' => 'boolean',
        ]);

        $plan = RatePlan::create($data);

        return $this->success($plan, [], 201);
    }

    /**
     * Sync LCR data to Redis
     */
    public function syncLcr(): JsonResponse
    {
        $count = $this->lcrService->syncToRedis();

        return $this->success([
            'synced_prefixes' => $count,
            'synced_at' => now()->toISOString(),
        ]);
    }
}
