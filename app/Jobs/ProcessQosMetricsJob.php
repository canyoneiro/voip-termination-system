<?php

namespace App\Jobs;

use App\Models\Cdr;
use App\Services\QosService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessQosMetricsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [10, 30, 60];

    public function __construct(
        public int $cdrId
    ) {
        $this->onQueue('qos');
    }

    public function handle(QosService $qosService): void
    {
        $cdr = Cdr::find($this->cdrId);
        if (!$cdr) {
            return;
        }

        $qosService->processCallQos($cdr);
    }

    public function tags(): array
    {
        return ['qos', 'cdr:' . $this->cdrId];
    }
}
