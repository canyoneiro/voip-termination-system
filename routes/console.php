<?php

use App\Jobs\AnalyzeFraudPatternsJob;
use App\Jobs\CalculateQosDailyStatsJob;
use App\Jobs\GenerateScheduledReportJob;
use App\Models\ScheduledReport;
use Illuminate\Support\Facades\Schedule;

// Cleanup tasks
Schedule::command('cleanup:all')->dailyAt('01:00');
Schedule::command('blacklist:cleanup')->hourly();

// Statistics
Schedule::command('stats:daily')->dailyAt('00:05');

// Reset counters
Schedule::command('minutes:reset-daily')->dailyAt('00:01');
Schedule::command('minutes:reset-monthly')->monthlyOn(1, '00:10');

// Queue restart to pick up any code changes
Schedule::command('queue:restart')->dailyAt('04:00');

// QoS daily stats calculation
Schedule::call(function () {
    CalculateQosDailyStatsJob::dispatch(now()->subDay()->toDateString());
})->dailyAt('02:00')->name('qos-daily-stats');

// Fraud pattern analysis (every 5 minutes)
Schedule::call(function () {
    AnalyzeFraudPatternsJob::dispatch();
})->everyFiveMinutes()->name('fraud-analysis');

// Scheduled reports processing
Schedule::call(function () {
    $reports = ScheduledReport::where('active', true)
        ->where(function ($query) {
            $query->whereNull('last_run_at')
                ->orWhere(function ($q) {
                    // Daily reports
                    $q->where('frequency', 'daily')
                        ->where('last_run_at', '<', now()->subDay());
                })
                ->orWhere(function ($q) {
                    // Weekly reports (run on Mondays)
                    $q->where('frequency', 'weekly')
                        ->where('last_run_at', '<', now()->subWeek());
                })
                ->orWhere(function ($q) {
                    // Monthly reports (run on 1st of month)
                    $q->where('frequency', 'monthly')
                        ->where('last_run_at', '<', now()->subMonth());
                });
        })
        ->get();

    foreach ($reports as $report) {
        // Check if it's the right time based on frequency
        $shouldRun = false;

        if ($report->frequency === 'daily') {
            $shouldRun = true;
        } elseif ($report->frequency === 'weekly' && now()->dayOfWeek === 1) {
            $shouldRun = true;
        } elseif ($report->frequency === 'monthly' && now()->day === 1) {
            $shouldRun = true;
        }

        if ($shouldRun) {
            GenerateScheduledReportJob::dispatch($report);
        }
    }
})->dailyAt('06:00')->name('scheduled-reports');
