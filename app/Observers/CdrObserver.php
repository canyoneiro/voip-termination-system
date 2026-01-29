<?php

namespace App\Observers;

use App\Jobs\AnalyzeFraudPatternsJob;
use App\Jobs\ProcessCdrBillingJob;
use App\Jobs\ProcessQosMetricsJob;
use App\Models\Cdr;
use App\Services\FraudDetectionService;

class CdrObserver
{
    public function __construct(
        protected FraudDetectionService $fraudService
    ) {}

    /**
     * Handle the Cdr "created" event.
     */
    public function created(Cdr $cdr): void
    {
        // Dispatch billing calculation job
        ProcessCdrBillingJob::dispatch($cdr);

        // Dispatch QoS metrics processing job (only for answered calls)
        if ($cdr->sip_code === 200 && $cdr->duration > 0) {
            ProcessQosMetricsJob::dispatch($cdr);
        }

        // Perform real-time fraud checks
        $this->performFraudChecks($cdr);
    }

    /**
     * Perform real-time fraud detection checks.
     */
    protected function performFraudChecks(Cdr $cdr): void
    {
        // Check for high-cost destinations in real-time
        if ($cdr->customer_id) {
            try {
                // Quick check for premium/high-cost destinations
                $this->fraudService->analyzeCall($cdr);
            } catch (\Exception $e) {
                // Log error but don't fail CDR creation
                \Log::error('Fraud detection error for CDR ' . $cdr->uuid . ': ' . $e->getMessage());
            }
        }
    }

    /**
     * Handle the Cdr "updated" event.
     */
    public function updated(Cdr $cdr): void
    {
        // If the CDR was updated with final data (e.g., from BYE processing)
        // and billing wasn't calculated yet, dispatch the billing job
        if ($cdr->wasChanged(['duration', 'billable_duration', 'sip_code']) && !$cdr->cost) {
            ProcessCdrBillingJob::dispatch($cdr);
        }

        // If the call was just answered, process QoS
        if ($cdr->wasChanged('sip_code') && $cdr->sip_code === 200 && $cdr->duration > 0) {
            ProcessQosMetricsJob::dispatch($cdr);
        }
    }
}
