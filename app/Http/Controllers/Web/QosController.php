<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Carrier;
use App\Models\Customer;
use App\Models\QosDailyStat;
use App\Models\QosMetric;
use App\Services\QosService;
use Illuminate\Http\Request;

class QosController extends Controller
{
    public function __construct(
        protected QosService $qosService
    ) {}

    public function index(Request $request)
    {
        // Real-time metrics
        $realtime = $this->qosService->getRealtimeQos();

        // Quality distribution (last 24h)
        $distribution = QosMetric::where('created_at', '>=', now()->subHours(24))
            ->selectRaw('quality_rating, COUNT(*) as count')
            ->groupBy('quality_rating')
            ->pluck('count', 'quality_rating')
            ->toArray();

        // Poor quality calls (MOS < 3.0)
        $poorCalls = QosMetric::with(['cdr.customer', 'cdr.carrier'])
            ->where('created_at', '>=', now()->subHours(24))
            ->where('mos_score', '<', 3.0)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        // Hourly trend (last 24h)
        $hourlyTrend = QosMetric::where('created_at', '>=', now()->subHours(24))
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m-%d %H:00") as hour, AVG(mos_score) as avg_mos, AVG(pdd) as avg_pdd')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        // Carrier comparison
        $carrierStats = QosMetric::where('qos_metrics.created_at', '>=', now()->subHours(24))
            ->join('cdrs', 'qos_metrics.cdr_id', '=', 'cdrs.id')
            ->join('carriers', 'cdrs.carrier_id', '=', 'carriers.id')
            ->selectRaw('carriers.name as carrier_name, AVG(qos_metrics.mos_score) as avg_mos, AVG(qos_metrics.pdd) as avg_pdd, COUNT(*) as calls')
            ->groupBy('carriers.id', 'carriers.name')
            ->orderByDesc('avg_mos')
            ->get();

        return view('qos.index', compact(
            'realtime',
            'distribution',
            'poorCalls',
            'hourlyTrend',
            'carrierStats'
        ));
    }

    public function customer(Customer $customer)
    {
        $stats = $this->qosService->getCustomerQos($customer->id);

        $metrics = QosMetric::whereHas('cdr', fn($q) => $q->where('customer_id', $customer->id))
            ->where('created_at', '>=', now()->subDays(7))
            ->orderByDesc('created_at')
            ->paginate(50);

        $dailyStats = QosDailyStat::where('customer_id', $customer->id)
            ->where('date', '>=', now()->subDays(30))
            ->orderBy('date')
            ->get();

        return view('qos.customer', compact('customer', 'stats', 'metrics', 'dailyStats'));
    }

    public function carrier(Carrier $carrier)
    {
        $stats = $this->qosService->getCarrierQos($carrier->id);

        $metrics = QosMetric::whereHas('cdr', fn($q) => $q->where('carrier_id', $carrier->id))
            ->where('created_at', '>=', now()->subDays(7))
            ->orderByDesc('created_at')
            ->paginate(50);

        $dailyStats = QosDailyStat::where('carrier_id', $carrier->id)
            ->where('date', '>=', now()->subDays(30))
            ->orderBy('date')
            ->get();

        return view('qos.carrier', compact('carrier', 'stats', 'metrics', 'dailyStats'));
    }
}
