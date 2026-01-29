<?php

namespace App\Http\Controllers\Api;

use App\Models\ActiveCall;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ActiveCallController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = ActiveCall::with(['customer:id,name', 'carrier:id,name']);

        if ($request->has('customer_id')) {
            $query->where('customer_id', $request->input('customer_id'));
        }

        if ($request->has('carrier_id')) {
            $query->where('carrier_id', $request->input('carrier_id'));
        }

        $calls = $query->orderByDesc('start_time')->get();

        // Add duration to each call
        foreach ($calls as $call) {
            $call->current_duration = $call->duration;
            $call->current_duration_formatted = $call->duration_formatted;
        }

        return $this->success($calls);
    }

    public function count(Request $request): JsonResponse
    {
        $query = ActiveCall::query();

        $byCustomer = ActiveCall::selectRaw('customer_id, COUNT(*) as count')
            ->groupBy('customer_id')
            ->pluck('count', 'customer_id');

        $byCarrier = ActiveCall::selectRaw('carrier_id, COUNT(*) as count')
            ->whereNotNull('carrier_id')
            ->groupBy('carrier_id')
            ->pluck('count', 'carrier_id');

        return $this->success([
            'total' => ActiveCall::count(),
            'by_customer' => $byCustomer,
            'by_carrier' => $byCarrier,
        ]);
    }

    public function show(string $callId): JsonResponse
    {
        $call = ActiveCall::with(['customer', 'carrier'])
            ->where('call_id', $callId)
            ->first();

        if (!$call) {
            return $this->notFound('Active call not found');
        }

        $call->current_duration = $call->duration;

        return $this->success($call);
    }
}
