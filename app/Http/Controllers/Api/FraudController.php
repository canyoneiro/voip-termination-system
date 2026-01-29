<?php

namespace App\Http\Controllers\Api;

use App\Models\Customer;
use App\Models\FraudIncident;
use App\Models\FraudRule;
use App\Services\FraudDetectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FraudController extends BaseApiController
{
    public function __construct(
        protected FraudDetectionService $fraudService
    ) {}

    /**
     * List fraud incidents
     */
    public function incidents(Request $request): JsonResponse
    {
        $query = FraudIncident::with(['customer', 'fraudRule']);

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->input('customer_id'));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('severity')) {
            $query->where('severity', $request->input('severity'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        } else {
            // Default to unresolved
            $query->unresolved();
        }

        if ($request->filled('from')) {
            $query->where('created_at', '>=', $request->input('from'));
        }

        if ($request->filled('to')) {
            $query->where('created_at', '<=', $request->input('to'));
        }

        $perPage = min($request->input('per_page', 50), 200);
        $incidents = $query->orderByDesc('created_at')->paginate($perPage);

        return $this->paginated($incidents);
    }

    /**
     * Get single incident
     */
    public function showIncident(FraudIncident $incident): JsonResponse
    {
        $incident->load(['customer', 'fraudRule', 'cdr', 'resolvedBy']);

        return $this->success($incident);
    }

    /**
     * Update incident status
     */
    public function updateIncident(Request $request, FraudIncident $incident): JsonResponse
    {
        $data = $request->validate([
            'status' => 'in:pending,investigating,false_positive,confirmed,resolved',
            'resolution_notes' => 'nullable|string|max:1000',
        ]);

        $userId = auth()->id() ?? 1; // Fallback for API tokens

        if (in_array($data['status'] ?? null, ['resolved', 'false_positive'])) {
            if ($data['status'] === 'false_positive') {
                $incident->markAsFalsePositive($userId, $data['resolution_notes'] ?? null);
            } else {
                $incident->resolve($userId, $data['resolution_notes'] ?? null);
            }
        } else {
            $incident->update($data);
        }

        return $this->success($incident->fresh());
    }

    /**
     * List fraud rules
     */
    public function rules(Request $request): JsonResponse
    {
        $query = FraudRule::with('customer');

        if ($request->filled('customer_id')) {
            $query->forCustomer($request->input('customer_id'));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->boolean('active', true)) {
            $query->active();
        }

        $rules = $query->orderBy('name')->get();

        return $this->success($rules);
    }

    /**
     * Get single rule
     */
    public function showRule(FraudRule $rule): JsonResponse
    {
        $rule->load('customer');
        $rule->loadCount('incidents');

        return $this->success($rule);
    }

    /**
     * Create fraud rule
     */
    public function storeRule(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'type' => 'required|in:' . implode(',', array_keys(FraudRule::TYPES)),
            'parameters' => 'required|array',
            'threshold' => 'nullable|numeric',
            'action' => 'required|in:log,alert,throttle,block',
            'severity' => 'required|in:low,medium,high,critical',
            'customer_id' => 'nullable|exists:customers,id',
            'cooldown_minutes' => 'integer|min:1|max:10080',
            'active' => 'boolean',
        ]);

        $rule = FraudRule::create($data);

        return $this->success($rule, [], 201);
    }

    /**
     * Update fraud rule
     */
    public function updateRule(Request $request, FraudRule $rule): JsonResponse
    {
        $data = $request->validate([
            'name' => 'string|max:100',
            'description' => 'nullable|string',
            'parameters' => 'array',
            'threshold' => 'nullable|numeric',
            'action' => 'in:log,alert,throttle,block',
            'severity' => 'in:low,medium,high,critical',
            'cooldown_minutes' => 'integer|min:1|max:10080',
            'active' => 'boolean',
        ]);

        $rule->update($data);

        return $this->success($rule);
    }

    /**
     * Delete fraud rule
     */
    public function destroyRule(FraudRule $rule): JsonResponse
    {
        $rule->delete();

        return $this->success(['deleted' => true]);
    }

    /**
     * Get risk scores for customers
     */
    public function riskScores(Request $request): JsonResponse
    {
        $query = Customer::where('active', true);

        if ($request->filled('min_score')) {
            // We'll filter after calculation
        }

        $customers = $query->get()->map(function ($customer) {
            return [
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'risk_score' => $this->fraudService->calculateRiskScore($customer),
            ];
        });

        if ($request->filled('min_score')) {
            $minScore = (int) $request->input('min_score');
            $customers = $customers->filter(fn($c) => $c['risk_score'] >= $minScore);
        }

        $customers = $customers->sortByDesc('risk_score')->values();

        return $this->success($customers);
    }

    /**
     * Get fraud statistics
     */
    public function stats(Request $request): JsonResponse
    {
        $from = $request->input('from', now()->subDays(30)->toDateString());
        $to = $request->input('to', now()->toDateString());

        $stats = $this->fraudService->getStats($from, $to);

        return $this->success($stats);
    }

    /**
     * Get available fraud types and actions
     */
    public function types(): JsonResponse
    {
        return $this->success([
            'types' => FraudRule::TYPES,
            'actions' => FraudRule::ACTIONS,
            'severities' => FraudRule::SEVERITIES,
        ]);
    }
}
