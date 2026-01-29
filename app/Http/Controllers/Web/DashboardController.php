<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ActiveCall;
use App\Models\Alert;
use App\Models\Carrier;
use App\Models\Cdr;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class DashboardController extends Controller
{
    public function index()
    {
        // Active calls
        $activeCalls = ActiveCall::with(['customer', 'carrier'])->get();
        $activeCallsCount = $activeCalls->count();

        // Real-time stats
        $cps = (int) Redis::get('voip:cps') ?? 0;

        // Carriers status
        $carriers = Carrier::select('id', 'name', 'host', 'state', 'max_channels', 'last_options_time')
            ->withCount(['activeCalls'])
            ->get();

        // Unacknowledged alerts
        $alerts = Alert::where('acknowledged', false)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Today's stats
        $today = now()->toDateString();
        $todayStats = Cdr::whereDate('start_time', $today)
            ->selectRaw('
                COUNT(*) as total_calls,
                SUM(CASE WHEN answer_time IS NOT NULL THEN 1 ELSE 0 END) as answered,
                SUM(duration) as total_duration
            ')
            ->first();

        $asr = $todayStats->total_calls > 0
            ? round(($todayStats->answered / $todayStats->total_calls) * 100, 1)
            : 0;

        $acd = $todayStats->answered > 0
            ? round($todayStats->total_duration / $todayStats->answered)
            : 0;

        // Hourly calls for chart (last 24 hours)
        $hourlyStats = Cdr::where('start_time', '>=', now()->subHours(24))
            ->selectRaw('DATE_FORMAT(start_time, "%Y-%m-%d %H:00:00") as hour')
            ->selectRaw('COUNT(*) as calls')
            ->selectRaw('SUM(CASE WHEN answer_time IS NOT NULL THEN 1 ELSE 0 END) as answered')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        // Top customers by active calls
        $topCustomers = Customer::withCount('activeCalls')
            ->having('active_calls_count', '>', 0)
            ->orderByDesc('active_calls_count')
            ->limit(5)
            ->get();

        return view('dashboard', compact(
            'activeCalls',
            'activeCallsCount',
            'cps',
            'carriers',
            'alerts',
            'todayStats',
            'asr',
            'acd',
            'hourlyStats',
            'topCustomers'
        ));
    }
}
