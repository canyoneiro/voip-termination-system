<?php

namespace App\Console\Commands;

use App\Models\Alert;
use App\Models\SystemSetting;
use Illuminate\Console\Command;

class CleanupAlerts extends Command
{
    protected $signature = 'cleanup:alerts {--days= : Days to retain}';
    protected $description = 'Clean up old alert records';

    public function handle()
    {
        $days = $this->option('days')
            ?? SystemSetting::getValue('retention', 'alerts_days', 30);

        $cutoff = now()->subDays($days);

        $count = Alert::where('created_at', '<', $cutoff)
            ->where('acknowledged', 1)
            ->delete();

        $this->info("Deleted {$count} acknowledged alerts older than {$days} days");

        return 0;
    }
}
