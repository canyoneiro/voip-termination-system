<?php

namespace App\Http\Controllers\Api;

use App\Models\Alert;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AlertController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Alert::query();

        if ($request->has('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->has('severity')) {
            $query->where('severity', $request->input('severity'));
        }

        if ($request->has('acknowledged')) {
            $query->where('acknowledged', $request->boolean('acknowledged'));
        }

        if ($request->has('from')) {
            $query->where('created_at', '>=', $request->input('from'));
        }

        if ($request->has('to')) {
            $query->where('created_at', '<=', $request->input('to'));
        }

        $perPage = min($request->input('per_page', 50), 100);
        $alerts = $query->orderByDesc('created_at')->paginate($perPage);

        return $this->paginated($alerts);
    }

    public function show(int $id): JsonResponse
    {
        $alert = Alert::with('acknowledgedBy:id,name')->find($id);

        if (!$alert) {
            return $this->notFound('Alert not found');
        }

        return $this->success($alert);
    }

    public function acknowledge(Request $request, int $id): JsonResponse
    {
        $alert = Alert::find($id);

        if (!$alert) {
            return $this->notFound('Alert not found');
        }

        if ($alert->acknowledged) {
            return $this->error('Alert already acknowledged', 'ALREADY_ACKNOWLEDGED');
        }

        $alert->update([
            'acknowledged' => true,
            'acknowledged_by' => auth()->id(),
            'acknowledged_at' => now(),
        ]);

        return $this->success(['acknowledged' => true]);
    }

    public function acknowledgeBulk(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer',
        ]);

        $count = Alert::whereIn('id', $validated['ids'])
            ->where('acknowledged', false)
            ->update([
                'acknowledged' => true,
                'acknowledged_by' => auth()->id(),
                'acknowledged_at' => now(),
            ]);

        return $this->success(['acknowledged_count' => $count]);
    }

    public function unacknowledgedCount(): JsonResponse
    {
        $count = Alert::where('acknowledged', false)->count();
        $criticalCount = Alert::where('acknowledged', false)->where('severity', 'critical')->count();

        return $this->success([
            'total' => $count,
            'critical' => $criticalCount,
        ]);
    }
}
