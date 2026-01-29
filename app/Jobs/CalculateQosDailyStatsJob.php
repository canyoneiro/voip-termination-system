<?php

namespace App\Jobs;

use App\Models\Carrier;
use App\Models\Customer;
use App\Services\QosService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CalculateQosDailyStatsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 600;

    public function __construct(
        public string $date
    ) {
        $this->onQueue('stats');
    }

    public function handle(QosService $qosService): void
    {
        // Global stats (no customer/carrier filter)
        $qosService->calculateDailyStats($this->date, null, null);

        // Per customer stats
        $customers = Customer::where('active', true)->get();
        foreach ($customers as $customer) {
            $qosService->calculateDailyStats($this->date, $customer->id, null);
        }

        // Per carrier stats
        $carriers = Carrier::whereIn('state', ['active', 'probing'])->get();
        foreach ($carriers as $carrier) {
            $qosService->calculateDailyStats($this->date, null, $carrier->id);
        }
    }

    public function tags(): array
    {
        return ['qos', 'daily-stats', 'date:' . $this->date];
    }
}
