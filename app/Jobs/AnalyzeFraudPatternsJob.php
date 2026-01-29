<?php

namespace App\Jobs;

use App\Services\FraudDetectionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AnalyzeFraudPatternsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 300;

    public function __construct()
    {
        $this->onQueue('fraud');
    }

    public function handle(FraudDetectionService $fraudService): void
    {
        $incidents = $fraudService->runPeriodicAnalysis();

        if (count($incidents) > 0) {
            Log::info("Fraud analysis completed", [
                'incidents_created' => count($incidents),
            ]);
        }
    }

    public function tags(): array
    {
        return ['fraud', 'analysis'];
    }
}
