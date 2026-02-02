<?php

use App\Jobs\AnalyzeFraudPatternsJob;
use App\Jobs\CalculateQosDailyStatsJob;
use App\Jobs\CheckThresholdsJob;
use App\Jobs\GenerateScheduledReportJob;
use App\Jobs\ProcessPendingAlertsJob;
use App\Jobs\SyncCarrierStatesJob;
use App\Jobs\SyncSettingsToRedisJob;
use App\Models\ScheduledReport;
use Illuminate\Support\Facades\Schedule;

// CRITICAL: Process alerts from Kamailio that bypass Eloquent observers
// This ensures all alerts get email/Telegram notifications
Schedule::call(function () {
    ProcessPendingAlertsJob::dispatch();
})->everyMinute()->name('process-pending-alerts');

// CRITICAL: Sync carrier states from Kamailio dispatcher to database
// This ensures CarrierObserver gets triggered for carrier_down/recovered events
Schedule::call(function () {
    SyncCarrierStatesJob::dispatch();
})->everyMinute()->name('sync-carrier-states');

// Check system thresholds and generate alerts
// Uses settings: alerts/channels_warning_pct, alerts/minutes_warning_pct,
// alerts/min_asr_global, alerts/options_timeout
Schedule::call(function () {
    CheckThresholdsJob::dispatch();
})->everyMinute()->name('check-thresholds');

// Sync critical settings to Redis for Kamailio to read
// Uses settings: limits/global_max_*, security/flood_threshold,
// security/blacklist_duration, security/whitelist_ips
Schedule::call(function () {
    SyncSettingsToRedisJob::dispatch();
})->everyMinute()->name('sync-settings-redis');

// Cleanup tasks
Schedule::command('cleanup:all')->dailyAt('01:00');
Schedule::command('blacklist:cleanup')->hourly();
Schedule::command('calls:cleanup-stale')->everyFiveMinutes()->name('cleanup-stale-calls');

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

// Scheduled reports processing - runs every minute to check for due reports
// Uses next_run_at and send_time from ScheduledReport model
Schedule::call(function () {
    // Get reports that are due (next_run_at <= now or null with proper frequency conditions)
    $reports = ScheduledReport::where('active', true)
        ->where(function ($query) {
            $query->whereNotNull('next_run_at')
                ->where('next_run_at', '<=', now());
        })
        ->orWhere(function ($query) {
            // First run: no next_run_at set yet
            $query->where('active', true)
                ->whereNull('next_run_at')
                ->whereNull('last_sent_at');
        })
        ->get();

    foreach ($reports as $report) {
        // Dispatch the job to generate and send the report
        GenerateScheduledReportJob::dispatch($report);

        // Calculate and set next_run_at based on frequency and send_time
        $nextRun = $report->calculateNextRun();
        $report->update([
            'last_sent_at' => now(),
            'next_run_at' => $nextRun,
        ]);
    }
})->everyMinute()->name('scheduled-reports');

// Initialize next_run_at for reports that don't have it set (run once at startup)
Schedule::call(function () {
    ScheduledReport::where('active', true)
        ->whereNull('next_run_at')
        ->each(function ($report) {
            $nextRun = $report->calculateNextRun();
            if ($nextRun) {
                $report->update(['next_run_at' => $nextRun]);
            }
        });
})->dailyAt('00:00')->name('init-report-schedules');
