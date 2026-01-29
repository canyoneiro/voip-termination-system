<?php

namespace App\Console\Commands;

use App\Models\Cdr;
use App\Models\DailyStat;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class StatsDaily extends Command
{
    protected $signature = 'stats:daily {--date= : Date to calculate (Y-m-d)}';
    protected $description = 'Calculate daily statistics';

    public function handle()
    {
        $date = $this->option('date')
            ? \Carbon\Carbon::parse($this->option('date'))
            : now()->subDay();

        $dateStr = $date->format('Y-m-d');

        $this->info("Calculating stats for {$dateStr}...");

        // Global stats
        $this->calculateStats($dateStr, null, null);

        // Per customer
        $customers = DB::table('cdrs')
            ->whereDate('start_time', $dateStr)
            ->distinct()
            ->pluck('customer_id');

        foreach ($customers as $customerId) {
            $this->calculateStats($dateStr, $customerId, null);
        }

        // Per carrier
        $carriers = DB::table('cdrs')
            ->whereDate('start_time', $dateStr)
            ->whereNotNull('carrier_id')
            ->distinct()
            ->pluck('carrier_id');

        foreach ($carriers as $carrierId) {
            $this->calculateStats($dateStr, null, $carrierId);
        }

        $this->info('Daily stats calculated successfully');

        return 0;
    }

    protected function calculateStats($date, $customerId, $carrierId)
    {
        $query = Cdr::whereDate('start_time', $date);

        if ($customerId) {
            $query->where('customer_id', $customerId);
        }
        if ($carrierId) {
            $query->where('carrier_id', $carrierId);
        }

        $stats = $query->selectRaw('
            COUNT(*) as total_calls,
            SUM(CASE WHEN answer_time IS NOT NULL THEN 1 ELSE 0 END) as answered_calls,
            SUM(CASE WHEN answer_time IS NULL THEN 1 ELSE 0 END) as failed_calls,
            SUM(duration) as total_duration,
            SUM(billable_duration) as billable_duration,
            AVG(CASE WHEN answer_time IS NOT NULL THEN pdd END) as avg_pdd
        ')->first();

        $asr = $stats->total_calls > 0
            ? round(($stats->answered_calls / $stats->total_calls) * 100, 2)
            : null;

        $acd = $stats->answered_calls > 0
            ? round($stats->total_duration / $stats->answered_calls, 2)
            : null;

        DailyStat::updateOrCreate(
            [
                'date' => $date,
                'customer_id' => $customerId,
                'carrier_id' => $carrierId,
            ],
            [
                'total_calls' => $stats->total_calls ?? 0,
                'answered_calls' => $stats->answered_calls ?? 0,
                'failed_calls' => $stats->failed_calls ?? 0,
                'total_duration' => $stats->total_duration ?? 0,
                'billable_duration' => $stats->billable_duration ?? 0,
                'asr' => $asr,
                'acd' => $acd,
                'avg_pdd' => $stats->avg_pdd,
            ]
        );
    }
}
