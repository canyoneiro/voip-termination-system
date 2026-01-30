<?php

namespace App\Observers;

use App\Models\Carrier;
use App\Models\KamailioDispatcher;
use App\Services\WebhookService;
use Illuminate\Support\Facades\Log;

class CarrierObserver
{
    protected WebhookService $webhookService;

    public function __construct(WebhookService $webhookService)
    {
        $this->webhookService = $webhookService;
    }

    /**
     * Handle the Carrier "created" event.
     */
    public function created(Carrier $carrier): void
    {
        $this->syncToKamailio($carrier, 'created');
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

        // Sync to Kamailio when carrier changes
        $this->syncToKamailio($carrier, 'updated');
    }

    /**
     * Handle the Carrier "deleted" event.
     */
    public function deleted(Carrier $carrier): void
    {
        $this->syncToKamailio($carrier, 'deleted');
    }

    /**
     * Sincroniza el carrier con Kamailio dispatcher
     */
    protected function syncToKamailio(Carrier $carrier, string $action): void
    {
        try {
            $result = KamailioDispatcher::syncAndReload();

            Log::info("Kamailio dispatcher sync after carrier {$action}", [
                'carrier_id' => $carrier->id,
                'carrier_name' => $carrier->name,
                'carrier_state' => $carrier->state,
                'synced_count' => $result['synced'],
                'reloaded' => $result['reloaded'],
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to sync Kamailio dispatcher after carrier {$action}", [
                'carrier_id' => $carrier->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
