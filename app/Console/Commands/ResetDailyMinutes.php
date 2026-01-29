<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\Carrier;
use Illuminate\Console\Command;

class ResetDailyMinutes extends Command
{
    protected $signature = 'minutes:reset-daily';
    protected $description = 'Reset daily minute counters for all customers and carriers';

    public function handle()
    {
        Customer::query()->update(['used_daily_minutes' => 0]);
        Carrier::query()->update([
            'daily_calls' => 0,
            'daily_minutes' => 0,
            'daily_failed' => 0,
        ]);

        $this->info('Daily minute counters reset successfully');

        return 0;
    }
}
