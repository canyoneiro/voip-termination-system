<?php

namespace App\Http\Controllers\Api;

use App\Models\Carrier;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Redis;

class CarrierController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Carrier::query();

        if ($request->has('active')) {
            $query->where('state', $request->boolean('active') ? 'active' : 'inactive');
        }

        if ($request->has('down')) {
            $query->whereIn('state', ['inactive', 'probing', 'disabled']);
        }

        $carriers = $query->orderBy('priority')->orderBy('name')->get();

        // Add real-time stats
        foreach ($carriers as $carrier) {
            $carrier->active_calls = $carrier->activeCalls()->count();
        }

        return $this->success($carriers);
    }

    public function show(int $id): JsonResponse
    {
        $carrier = Carrier::with('ips')->find($id);

        if (!$carrier) {
            return $this->notFound('Carrier not found');
        }

        $carrier->active_calls = $carrier->activeCalls()->count();

        return $this->success($carrier);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'host' => 'required|string|max:255',
            'port' => 'integer|min:1|max:65535',
            'transport' => 'in:udp,tcp,tls',
            'codecs' => 'nullable|string|max:255',
            'priority' => 'integer|min:1|max:100',
            'weight' => 'integer|min:1|max:1000',
            'tech_prefix' => 'nullable|string|max:50',
            'strip_digits' => 'integer|min:0|max:20',
            'prefix_filter' => 'nullable|string',
            'prefix_deny' => 'nullable|string',
            'max_cps' => 'integer|min:1|max:100',
            'max_channels' => 'integer|min:1|max:1000',
            'notes' => 'nullable|string',
        ]);

        $carrier = Carrier::create($validated);

        AuditLog::log('carrier.created', 'carrier', $carrier->id, null, $validated);

        return $this->success($carrier, [], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $carrier = Carrier::find($id);

        if (!$carrier) {
            return $this->notFound('Carrier not found');
        }

        $validated = $request->validate([
            'name' => 'string|max:100',
            'host' => 'string|max:255',
            'port' => 'integer|min:1|max:65535',
            'transport' => 'in:udp,tcp,tls',
            'codecs' => 'nullable|string|max:255',
            'priority' => 'integer|min:1|max:100',
            'weight' => 'integer|min:1|max:1000',
            'tech_prefix' => 'nullable|string|max:50',
            'strip_digits' => 'integer|min:0|max:20',
            'prefix_filter' => 'nullable|string',
            'prefix_deny' => 'nullable|string',
            'max_cps' => 'integer|min:1|max:100',
            'max_channels' => 'integer|min:1|max:1000',
            'notes' => 'nullable|string',
        ]);

        $oldValues = $carrier->toArray();
        $carrier->update($validated);

        AuditLog::log('carrier.updated', 'carrier', $carrier->id, $oldValues, $validated);

        return $this->success($carrier);
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $carrier = Carrier::find($id);

        if (!$carrier) {
            return $this->notFound('Carrier not found');
        }

        $validated = $request->validate([
            'state' => 'required|in:active,inactive,disabled',
        ]);

        $oldState = $carrier->state;
        $carrier->update(['state' => $validated['state']]);

        AuditLog::log('carrier.status.changed', 'carrier', $carrier->id, ['state' => $oldState], $validated);

        return $this->success(['state' => $carrier->state]);
    }

    public function destroy(int $id): JsonResponse
    {
        $carrier = Carrier::find($id);

        if (!$carrier) {
            return $this->notFound('Carrier not found');
        }

        if ($carrier->activeCalls()->count() > 0) {
            return $this->error('Cannot delete carrier with active calls', 'HAS_ACTIVE_CALLS', [], 409);
        }

        $carrierData = $carrier->toArray();
        $carrier->delete();

        AuditLog::log('carrier.deleted', 'carrier', $id, $carrierData, null);

        return $this->success(['deleted' => true]);
    }

    public function status(int $id): JsonResponse
    {
        $carrier = Carrier::find($id);

        if (!$carrier) {
            return $this->notFound('Carrier not found');
        }

        return $this->success([
            'state' => $carrier->state,
            'last_options_reply' => $carrier->last_options_reply,
            'last_options_time' => $carrier->last_options_time,
            'active_calls' => $carrier->activeCalls()->count(),
            'failover_count' => $carrier->failover_count,
            'daily_calls' => $carrier->daily_calls,
            'daily_failed' => $carrier->daily_failed,
            'asr_today' => $carrier->asr_today,
        ]);
    }
}
