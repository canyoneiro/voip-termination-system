<?php

namespace App\Console\Commands;

use App\Models\Cdr;
use App\Models\SystemSetting;
use Illuminate\Console\Command;

class CleanupCdrs extends Command
{
    protected $signature = 'cleanup:cdrs {--days= : Days to retain}';
    protected $description = 'Clean up old CDR records';

    public function handle()
    {
        $days = $this->option('days')
            ?? SystemSetting::getValue('retention', 'cdr_days', 90);

        // 0 = infinite retention, skip cleanup
        if ((int) $days === 0) {
            $this->info("CDR retention set to infinite (0 days), skipping cleanup");
            return 0;
        }

        $cutoff = now()->subDays($days);

        $count = Cdr::where('start_time', '<', $cutoff)->delete();

        $this->info("Deleted {$count} CDRs older than {$days} days");

        return 0;
    }
}
