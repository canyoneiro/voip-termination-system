<?php

namespace App\Jobs;

use App\Models\Cdr;
use App\Services\FraudDetectionService;
use App\Services\LcrService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessCdrBillingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [5, 15, 30];

    public function __construct(
        public int $cdrId
    ) {
        $this->onQueue('billing');
    }

    public function handle(LcrService $lcrService, FraudDetectionService $fraudService): void
    {
        $cdr = Cdr::find($this->cdrId);
        if (!$cdr) {
            return;
        }

        // Calculate billing
        $billing = $lcrService->calculateCdrBilling($cdr);
        $cdr->update($billing);

        // Analyze for fraud (only for answered calls)
        if ($cdr->isAnswered()) {
            $fraudService->analyzeCall($cdr);

            // Dispatch QoS processing
            ProcessQosMetricsJob::dispatch($cdr->id);
        }
    }

    public function tags(): array
    {
        return ['billing', 'cdr:' . $this->cdrId];
    }
}
