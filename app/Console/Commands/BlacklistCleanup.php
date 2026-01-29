<?php

namespace App\Console\Commands;

use App\Models\IpBlacklist;
use Illuminate\Console\Command;

class BlacklistCleanup extends Command
{
    protected $signature = 'blacklist:cleanup';
    protected $description = 'Remove expired entries from IP blacklist';

    public function handle()
    {
        $count = IpBlacklist::where('permanent', 0)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->delete();

        $this->info("Removed {$count} expired blacklist entries");

        return 0;
    }
}
