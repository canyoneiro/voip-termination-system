<?php

namespace App\Observers;

use App\Models\Carrier;
use App\Services\WebhookService;

class CarrierObserver
{
    protected WebhookService $webhookService;

    public function __construct(WebhookService $webhookService)
    {
        $this->webhookService = $webhookService;
    }

    public function updated(Carrier $carrier): void
    {
        // Check if state changed
        if ($carrier->isDirty('state')) {
            $oldState = $carrier->getOriginal('state');
            $newState = $carrier->state;

            // Carrier went down
            if ($oldState === 'active' && in_array($newState, ['inactive', 'probing', 'disabled'])) {
                $this->webhookService->carrierDown($carrier, "State changed from {$oldState} to {$newState}");
            }

            // Carrier recovered
            if (in_array($oldState, ['inactive', 'probing', 'disabled']) && $newState === 'active') {
                $this->webhookService->carrierRecovered($carrier);
            }
        }
    }
}
