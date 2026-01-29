<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\ActiveCall;
use App\Models\Cdr;
use App\Models\CustomerIp;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth('customer')->user();
        $customer = $user->customer;

        // Active calls count
        $activeCallsCount = ActiveCall::where('customer_id', $customer->id)->count();

        // Today's stats
        $todayStats = Cdr::where('customer_id', $customer->id)
            ->whereDate('start_time', today())
            ->selectRaw('
                COUNT(*) as total_calls,
                SUM(CASE WHEN sip_code = 200 THEN 1 ELSE 0 END) as answered_calls,
                SUM(billable_duration) as total_duration
            ')
            ->first();

        $asr = $todayStats->total_calls > 0
            ? round(($todayStats->answered_calls / $todayStats->total_calls) * 100, 1)
            : 0;

        // Build stats array for the view
        $stats = [
            'active_calls' => $activeCallsCount,
            'today_calls' => $todayStats->total_calls ?? 0,
            'today_answered' => $todayStats->answered_calls ?? 0,
            'today_minutes' => round(($todayStats->total_duration ?? 0) / 60),
            'asr' => $asr,
        ];

        // Recent calls (last 10)
        $recentCalls = Cdr::where('customer_id', $customer->id)
            ->orderByDesc('start_time')
            ->limit(10)
            ->get();

        // Authorized IPs
        $authorizedIps = CustomerIp::where('customer_id', $customer->id)
            ->orderByDesc('created_at')
            ->get();

        return view('portal.dashboard', compact(
            'customer',
            'stats',
            'recentCalls',
            'authorizedIps'
        ));
    }
}
