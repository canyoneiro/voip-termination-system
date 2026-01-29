<?php

namespace App\Console\Commands;

use App\Models\Customer;
use Illuminate\Console\Command;

class ResetMonthlyMinutes extends Command
{
    protected $signature = 'minutes:reset-monthly';
    protected $description = 'Reset monthly minute counters for all customers';

    public function handle()
    {
        Customer::query()->update(['used_monthly_minutes' => 0]);

        $this->info('Monthly minute counters reset successfully');

        return 0;
    }
}
