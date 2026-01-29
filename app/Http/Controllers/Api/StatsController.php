<?php

namespace App\Http\Controllers\Api;

use App\Models\Cdr;
use App\Models\Carrier;
use App\Models\Customer;
use App\Models\ActiveCall;
use App\Models\DailyStat;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class StatsController extends BaseApiController
{
    public function realtime(): JsonResponse
    {
        $activeCalls = ActiveCall::count();

        // CPS from Redis (sum of all customer CPS)
        $cps = 0;
        $keys = Redis::keys('voip:cps:*');
        foreach ($keys as $key) {
            $cps += (int) Redis::get($key);
        }

        // ASR last hour
        $lastHour = now()->subHour();
        $hourStats = Cdr::where('start_time', '>=', $lastHour)
            ->selectRaw('COUNT(*) as total, SUM(CASE WHEN sip_code = 200 THEN 1 ELSE 0 END) as answered')
            ->first();

        $asr1h = $hourStats->total > 0
            ? round(($hourStats->answered / $hourStats->total) * 100, 2)
            : null;

        // ACD last hour
        $acd1h = Cdr::where('start_time', '>=', $lastHour)
            ->where('sip_code', 200)
            ->where('duration', '>', 0)
            ->avg('duration');

        // Carriers status
        $carriersUp = Carrier::where('state', 'active')->count();
        $carriersDown = Carrier::whereIn('state', ['inactive', 'probing', 'disabled'])->count();

        return $this->success([
            'active_calls' => $activeCalls,
            'cps' => $cps,
            'asr_1h' => $asr1h,
            'acd_1h' => $acd1h ? round($acd1h, 2) : null,
            'carriers_up' => $carriersUp,
            'carriers_down' => $carriersDown,
        ]);
    }

    public function summary(Request $request): JsonResponse
    {
        $from = $request->input('from', now()->startOfDay()->toDateTimeString());
        $to = $request->input('to', now()->toDateTimeString());

        $stats = Cdr::whereBetween('start_time', [$from, $to])
            ->selectRaw('
                COUNT(*) as total_calls,
                SUM(CASE WHEN sip_code = 200 THEN 1 ELSE 0 END) as answered_calls,
                SUM(CASE WHEN sip_code >= 400 THEN 1 ELSE 0 END) as failed_calls,
                SUM(duration) as total_duration,
                SUM(billable_duration) as billable_duration,
                AVG(CASE WHEN sip_code = 200 THEN duration ELSE NULL END) as avg_duration,
                AVG(pdd) as avg_pdd
            ')
            ->first();

        $asr = $stats->total_calls > 0
            ? round(($stats->answered_calls / $stats->total_calls) * 100, 2)
            : null;

        return $this->success([
            'period' => ['from' => $from, 'to' => $to],
            'total_calls' => (int) $stats->total_calls,
            'answered_calls' => (int) $stats->answered_calls,
            'failed_calls' => (int) $stats->failed_calls,
            'total_duration' => (int) $stats->total_duration,
            'billable_duration' => (int) $stats->billable_duration,
            'asr' => $asr,
            'acd' => $stats->avg_duration ? round($stats->avg_duration, 2) : null,
            'avg_pdd' => $stats->avg_pdd ? round($stats->avg_pdd) : null,
        ]);
    }

    public function hourly(Request $request): JsonResponse
    {
        $date = $request->input('date', now()->toDateString());

        // Use database-agnostic hour extraction
        $driver = DB::getDriverName();
        $hourExpr = $driver === 'sqlite'
            ? "cast(strftime('%H', start_time) as integer)"
            : 'HOUR(start_time)';

        $stats = Cdr::whereDate('start_time', $date)
            ->selectRaw("
                {$hourExpr} as hour,
                COUNT(*) as total_calls,
                SUM(CASE WHEN sip_code = 200 THEN 1 ELSE 0 END) as answered_calls,
                SUM(duration) as total_duration
            ")
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        return $this->success($stats);
    }

    public function daily(Request $request): JsonResponse
    {
        $from = $request->input('from', now()->subDays(30)->toDateString());
        $to = $request->input('to', now()->toDateString());

        $stats = DailyStat::whereBetween('date', [$from, $to])
            ->whereNull('customer_id')
            ->whereNull('carrier_id')
            ->orderBy('date')
            ->get();

        return $this->success($stats);
    }

    public function byCustomer(Request $request): JsonResponse
    {
        $from = $request->input('from', now()->startOfDay()->toDateTimeString());
        $to = $request->input('to', now()->toDateTimeString());

        $stats = Cdr::whereBetween('start_time', [$from, $to])
            ->selectRaw('
                customer_id,
                COUNT(*) as total_calls,
                SUM(CASE WHEN sip_code = 200 THEN 1 ELSE 0 END) as answered_calls,
                SUM(duration) as total_duration
            ')
            ->groupBy('customer_id')
            ->with('customer:id,name')
            ->get();

        return $this->success($stats);
    }

    public function byCarrier(Request $request): JsonResponse
    {
        $from = $request->input('from', now()->startOfDay()->toDateTimeString());
        $to = $request->input('to', now()->toDateTimeString());

        $stats = Cdr::whereBetween('start_time', [$from, $to])
            ->whereNotNull('carrier_id')
            ->selectRaw('
                carrier_id,
                COUNT(*) as total_calls,
                SUM(CASE WHEN sip_code = 200 THEN 1 ELSE 0 END) as answered_calls,
                SUM(duration) as total_duration,
                AVG(pdd) as avg_pdd
            ')
            ->groupBy('carrier_id')
            ->with('carrier:id,name')
            ->get();

        return $this->success($stats);
    }

    public function topDestinations(Request $request): JsonResponse
    {
        $from = $request->input('from', now()->startOfDay()->toDateTimeString());
        $to = $request->input('to', now()->toDateTimeString());
        $limit = min($request->input('limit', 20), 100);

        // Use substr() which works in both MySQL and SQLite
        $stats = Cdr::whereBetween('start_time', [$from, $to])
            ->selectRaw('
                substr(callee, 1, 4) as prefix,
                COUNT(*) as total_calls,
                SUM(CASE WHEN sip_code = 200 THEN 1 ELSE 0 END) as answered_calls,
                SUM(duration) as total_duration
            ')
            ->groupBy('prefix')
            ->orderByDesc('total_calls')
            ->limit($limit)
            ->get();

        return $this->success($stats);
    }
}
