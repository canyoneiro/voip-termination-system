<?php

namespace App\Http\Controllers\Api;

use App\Models\Customer;
use App\Models\CustomerIp;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Redis;

class CustomerController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Customer::with('ips');

        if ($request->has('active')) {
            $query->where('active', $request->boolean('active'));
        }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('company', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $perPage = min($request->input('per_page', 50), 100);
        $customers = $query->orderBy('name')->paginate($perPage);

        return $this->paginated($customers);
    }

    public function show(int $id): JsonResponse
    {
        $customer = Customer::with('ips')->find($id);

        if (!$customer) {
            return $this->notFound('Customer not found');
        }

        // Add real-time stats
        $customer->active_calls = $customer->activeCalls()->count();
        $customer->current_cps = (int) Redis::get("voip:cps:{$id}") ?: 0;

        return $this->success($customer);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'company' => 'nullable|string|max:150',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'max_channels' => 'integer|min:1|max:1000',
            'max_cps' => 'integer|min:1|max:100',
            'max_daily_minutes' => 'nullable|integer|min:0',
            'max_monthly_minutes' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
            'alert_email' => 'nullable|email|max:255',
            'alert_telegram_chat_id' => 'nullable|string|max:100',
        ]);

        $customer = Customer::create($validated);

        AuditLog::log('customer.created', 'customer', $customer->id, null, $validated);

        return $this->success($customer, [], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return $this->notFound('Customer not found');
        }

        $validated = $request->validate([
            'name' => 'string|max:100',
            'company' => 'nullable|string|max:150',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'max_channels' => 'integer|min:1|max:1000',
            'max_cps' => 'integer|min:1|max:100',
            'max_daily_minutes' => 'nullable|integer|min:0',
            'max_monthly_minutes' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
            'alert_email' => 'nullable|email|max:255',
            'alert_telegram_chat_id' => 'nullable|string|max:100',
        ]);

        $oldValues = $customer->toArray();
        $customer->update($validated);

        AuditLog::log('customer.updated', 'customer', $customer->id, $oldValues, $validated);

        return $this->success($customer);
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return $this->notFound('Customer not found');
        }

        $validated = $request->validate([
            'active' => 'required|boolean',
        ]);

        $oldActive = $customer->active;
        $customer->update(['active' => $validated['active']]);

        AuditLog::log(
            $validated['active'] ? 'customer.enabled' : 'customer.disabled',
            'customer',
            $customer->id,
            ['active' => $oldActive],
            ['active' => $validated['active']]
        );

        return $this->success(['active' => $customer->active]);
    }

    public function destroy(int $id): JsonResponse
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return $this->notFound('Customer not found');
        }

        // Check for active calls
        if ($customer->activeCalls()->count() > 0) {
            return $this->error('Cannot delete customer with active calls', 'HAS_ACTIVE_CALLS', [], 409);
        }

        $customerData = $customer->toArray();
        $customer->delete();

        AuditLog::log('customer.deleted', 'customer', $id, $customerData, null);

        return $this->success(['deleted' => true]);
    }

    public function ips(int $id): JsonResponse
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return $this->notFound('Customer not found');
        }

        return $this->success($customer->ips);
    }

    public function addIp(Request $request, int $id): JsonResponse
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return $this->notFound('Customer not found');
        }

        $validated = $request->validate([
            'ip_address' => 'required|ip',
            'description' => 'nullable|string|max:100',
        ]);

        // Check if IP already exists for this customer
        $existing = CustomerIp::where('customer_id', $id)
            ->where('ip_address', $validated['ip_address'])
            ->first();

        if ($existing) {
            return $this->error('IP address already exists for this customer', 'DUPLICATE_IP', [], 409);
        }

        $ip = CustomerIp::create([
            'customer_id' => $id,
            'ip_address' => $validated['ip_address'],
            'description' => $validated['description'] ?? null,
            'active' => true,
        ]);

        AuditLog::log('customer.ip.added', 'customer', $id, null, $validated);

        return $this->success($ip, [], 201);
    }

    public function removeIp(int $id, int $ipId): JsonResponse
    {
        $ip = CustomerIp::where('customer_id', $id)->where('id', $ipId)->first();

        if (!$ip) {
            return $this->notFound('IP not found');
        }

        $ipData = $ip->toArray();
        $ip->delete();

        AuditLog::log('customer.ip.removed', 'customer', $id, $ipData, null);

        return $this->success(['deleted' => true]);
    }

    public function activeCalls(int $id): JsonResponse
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return $this->notFound('Customer not found');
        }

        $calls = $customer->activeCalls()->with('carrier')->get();

        return $this->success($calls);
    }

    public function usage(int $id): JsonResponse
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return $this->notFound('Customer not found');
        }

        $activeCalls = $customer->activeCalls()->count();
        $currentCps = (int) Redis::get("voip:cps:{$id}") ?: 0;

        return $this->success([
            'active_calls' => $activeCalls,
            'max_channels' => $customer->max_channels,
            'channels_usage_pct' => $customer->max_channels > 0
                ? round(($activeCalls / $customer->max_channels) * 100, 2)
                : 0,
            'current_cps' => $currentCps,
            'max_cps' => $customer->max_cps,
            'used_daily_minutes' => $customer->used_daily_minutes,
            'max_daily_minutes' => $customer->max_daily_minutes,
            'daily_minutes_pct' => $customer->daily_minutes_percentage,
            'used_monthly_minutes' => $customer->used_monthly_minutes,
            'max_monthly_minutes' => $customer->max_monthly_minutes,
            'monthly_minutes_pct' => $customer->monthly_minutes_percentage,
        ]);
    }

    public function resetMinutes(Request $request, int $id): JsonResponse
    {
        $customer = Customer::find($id);

        if (!$customer) {
            return $this->notFound('Customer not found');
        }

        $resetDaily = $request->boolean('daily', true);
        $resetMonthly = $request->boolean('monthly', false);

        $updates = [];
        if ($resetDaily) {
            $updates['used_daily_minutes'] = 0;
        }
        if ($resetMonthly) {
            $updates['used_monthly_minutes'] = 0;
        }

        if (!empty($updates)) {
            $oldValues = $customer->only(array_keys($updates));
            $customer->update($updates);
            AuditLog::log('customer.minutes.reset', 'customer', $id, $oldValues, $updates);
        }

        return $this->success(['reset' => true]);
    }
}
