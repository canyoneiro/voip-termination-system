<?php

namespace App\Http\Controllers\Api;

use App\Models\Cdr;
use App\Models\SipTrace;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CdrController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Cdr::with(['customer:id,name', 'carrier:id,name']);

        // Filters
        if ($request->has('customer_id')) {
            $query->where('customer_id', $request->input('customer_id'));
        }

        if ($request->has('carrier_id')) {
            $query->where('carrier_id', $request->input('carrier_id'));
        }

        if ($request->has('caller')) {
            $query->where('caller', 'like', '%' . $request->input('caller') . '%');
        }

        if ($request->has('callee')) {
            $query->where('callee', 'like', '%' . $request->input('callee') . '%');
        }

        if ($request->has('from')) {
            $query->where('start_time', '>=', $request->input('from'));
        }

        if ($request->has('to')) {
            $query->where('start_time', '<=', $request->input('to'));
        }

        if ($request->has('min_duration')) {
            $query->where('duration', '>=', $request->input('min_duration'));
        }

        if ($request->has('sip_code')) {
            $query->where('sip_code', $request->input('sip_code'));
        }

        if ($request->boolean('answered_only')) {
            $query->where('sip_code', 200)->where('duration', '>', 0);
        }

        if ($request->boolean('failed_only')) {
            $query->where('sip_code', '>=', 400);
        }

        $perPage = min($request->input('per_page', 100), 500);
        $cdrs = $query->orderByDesc('start_time')->paginate($perPage);

        return $this->paginated($cdrs);
    }

    public function show(string $uuid): JsonResponse
    {
        $cdr = Cdr::with(['customer', 'carrier'])->where('uuid', $uuid)->first();

        if (!$cdr) {
            return $this->notFound('CDR not found');
        }

        return $this->success($cdr);
    }

    public function trace(string $uuid): JsonResponse
    {
        $cdr = Cdr::where('uuid', $uuid)->first();

        if (!$cdr) {
            return $this->notFound('CDR not found');
        }

        $traces = SipTrace::where('call_id', $cdr->call_id)
            ->orderBy('timestamp')
            ->get();

        return $this->success($traces);
    }

    public function export(Request $request): JsonResponse
    {
        // For now, just return a message - full export would generate CSV
        return $this->success(['message' => 'Export functionality available via web panel']);
    }
}
