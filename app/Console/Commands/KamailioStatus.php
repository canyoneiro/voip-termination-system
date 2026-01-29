<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\DB;

class KamailioStatus extends Command
{
    protected $signature = 'kamailio:status';
    protected $description = 'Show Kamailio status and statistics';

    public function handle()
    {
        // Service status
        $serviceResult = Process::run('systemctl is-active kamailio');
        $status = trim($serviceResult->output());

        $this->info("Kamailio Service: {$status}");
        $this->newLine();

        // Active calls from Redis
        try {
            $activeCalls = Redis::get('voip:active_calls') ?? 0;
            $this->info("Active Calls: {$activeCalls}");
        } catch (\Exception $e) {
            $this->warn("Could not get active calls from Redis");
        }

        // Active calls from database
        $dbActiveCalls = DB::table('active_calls')->count();
        $this->info("Active Calls (DB): {$dbActiveCalls}");
        $this->newLine();

        // Carrier statuses
        $this->info("Carrier Status:");
        $carriers = DB::table('carriers')
            ->select('name', 'host', 'state', 'last_options_time')
            ->orderBy('priority')
            ->get();

        $this->table(
            ['Name', 'Host', 'State', 'Last OPTIONS'],
            $carriers->map(fn($c) => [
                $c->name,
                $c->host,
                $c->state,
                $c->last_options_time ?? 'Never'
            ])
        );

        return 0;
    }
}
