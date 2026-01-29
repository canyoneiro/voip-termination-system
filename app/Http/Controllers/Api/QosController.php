<?php

namespace App\Http\Controllers\Api;

use App\Models\QosDailyStat;
use App\Models\QosMetric;
use App\Services\QosService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QosController extends BaseApiController
{
    public function __construct(
        protected QosService $qosService
    ) {}

    /**
     * Get realtime QoS metrics
     */
    public function realtime(Request $request): JsonResponse
    {
        $hours = min($request->input('hours', 1), 24);
        $metrics = $this->qosService->getRealtimeQos($hours);

        return $this->success($metrics);
    }

    /**
     * Get QoS trends
     */
    public function trends(Request $request): JsonResponse
    {
        $request->validate([
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
            'granularity' => 'in:hour,day,week,month',
        ]);

        $trends = $this->qosService->getTrends(
            $request->input('from'),
            $request->input('to'),
            $request->input('granularity', 'hour')
        );

        return $this->success($trends);
    }

    /**
     * Get QoS by customer
     */
    public function byCustomer(Request $request): JsonResponse
    {
        $request->validate([
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
        ]);

        $from = $request->input('from');
        $to = $request->input('to');

        $data = QosMetric::with('customer')
            ->whereBetween('call_time', [$from, $to])
            ->whereNotNull('customer_id')
            ->selectRaw('
                customer_id,
                COUNT(*) as calls,
                AVG(mos_score) as avg_mos,
                AVG(pdd) as avg_pdd,
                SUM(CASE WHEN quality_rating IN ("poor", "bad") THEN 1 ELSE 0 END) as poor_calls
            ')
            ->groupBy('customer_id')
            ->orderByDesc('calls')
            ->get()
            ->map(function ($row) {
                return [
                    'customer_id' => $row->customer_id,
                    'customer_name' => $row->customer?->name ?? 'Unknown',
                    'calls' => $row->calls,
                    'avg_mos' => $row->avg_mos ? round($row->avg_mos, 2) : null,
                    'avg_pdd' => $row->avg_pdd ? (int) $row->avg_pdd : null,
                    'poor_percentage' => $row->calls > 0
                        ? round(($row->poor_calls / $row->calls) * 100, 2)
                        : 0,
                ];
            });

        return $this->success($data);
    }

    /**
     * Get QoS by carrier
     */
    public function byCarrier(Request $request): JsonResponse
    {
        $request->validate([
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
        ]);

        $data = $this->qosService->getByCarrier(
            $request->input('from'),
            $request->input('to')
        );

        return $this->success($data);
    }

    /**
     * Get daily stats
     */
    public function dailyStats(Request $request): JsonResponse
    {
        $request->validate([
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
            'customer_id' => 'nullable|integer|exists:customers,id',
            'carrier_id' => 'nullable|integer|exists:carriers,id',
        ]);

        $query = QosDailyStat::whereBetween('date', [
            $request->input('from'),
            $request->input('to'),
        ]);

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->input('customer_id'));
        } else {
            $query->whereNull('customer_id');
        }

        if ($request->filled('carrier_id')) {
            $query->where('carrier_id', $request->input('carrier_id'));
        } else {
            $query->whereNull('carrier_id');
        }

        $stats = $query->orderBy('date')->get();

        return $this->success($stats);
    }

    /**
     * Get recent poor quality calls
     */
    public function poorCalls(Request $request): JsonResponse
    {
        $hours = min($request->input('hours', 24), 168);
        $limit = min($request->input('limit', 50), 200);

        $calls = QosMetric::with(['cdr', 'customer', 'carrier'])
            ->where('call_time', '>=', now()->subHours($hours))
            ->whereIn('quality_rating', ['poor', 'bad'])
            ->orderByDesc('call_time')
            ->limit($limit)
            ->get()
            ->map(function ($metric) {
                return [
                    'id' => $metric->id,
                    'call_time' => $metric->call_time,
                    'customer' => $metric->customer?->name,
                    'carrier' => $metric->carrier?->name,
                    'mos_score' => $metric->mos_score,
                    'pdd' => $metric->pdd,
                    'quality_rating' => $metric->quality_rating,
                    'codec' => $metric->codec_used,
                    'callee' => $metric->cdr?->callee,
                ];
            });

        return $this->success($calls);
    }
}
