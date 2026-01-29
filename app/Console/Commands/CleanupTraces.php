<?php

namespace App\Console\Commands;

use App\Models\SipTrace;
use App\Models\SystemSetting;
use Illuminate\Console\Command;

class CleanupTraces extends Command
{
    protected $signature = 'cleanup:traces {--days= : Days to retain}';
    protected $description = 'Clean up old SIP trace records';

    public function handle()
    {
        $days = $this->option('days')
            ?? SystemSetting::getValue('retention', 'trace_days', 7);

        $cutoff = now()->subDays($days);

        $count = SipTrace::where('timestamp', '<', $cutoff)->delete();

        $this->info("Deleted {$count} SIP traces older than {$days} days");

        return 0;
    }
}
