<?php

namespace App\Observers;

use App\Jobs\SyncFirewallJob;
use App\Models\Alert;
use App\Models\Carrier;
use App\Models\KamailioDispatcher;
use App\Services\WebhookService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CarrierObserver
{
    protected WebhookService $webhookService;

    /**
     * Minimum seconds between state change alerts for the same carrier.
     * This prevents alert storms from transient state changes.
     */
    protected const STATE_CHANGE_DEBOUNCE_SECONDS = 30;

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

            // Debounce: Skip alerts for rapid state changes (prevents alert storms)
            $debounceKey = "carrier_state_change:{$carrier->id}";
            $lastChange = Cache::get($debounceKey);

            if ($lastChange && (now()->timestamp - $lastChange) < self::STATE_CHANGE_DEBOUNCE_SECONDS) {
                Log::info("Carrier state change debounced (too rapid)", [
                    'carrier_id' => $carrier->id,
                    'carrier_name' => $carrier->name,
                    'old_state' => $oldState,
                    'new_state' => $newState,
                    'seconds_since_last' => now()->timestamp - $lastChange,
                ]);
                // Still sync to Kamailio, but don't create alerts
                $this->syncToKamailio($carrier, 'updated');
                return;
            }

            // Record this state change time
            Cache::put($debounceKey, now()->timestamp, self::STATE_CHANGE_DEBOUNCE_SECONDS * 2);

            // Carrier went down
            if ($oldState === 'active' && in_array($newState, ['inactive', 'probing', 'disabled'])) {
                $reason = "State changed from {$oldState} to {$newState}";

                // Create alert for carrier down
                Alert::create([
                    'type' => 'carrier_down',
                    'severity' => 'critical',
                    'source_type' => 'carrier',
                    'source_id' => $carrier->id,
                    'source_name' => $carrier->name,
                    'title' => "Carrier down: {$carrier->name}",
                    'message' => "Carrier {$carrier->name} ({$carrier->host}:{$carrier->port}) is now {$newState}. {$reason}",
                    'metadata' => [
                        'host' => $carrier->host,
                        'port' => $carrier->port,
                        'old_state' => $oldState,
                        'new_state' => $newState,
                    ],
                ]);

                // Also trigger webhook
                $this->webhookService->carrierDown($carrier, $reason);

                Log::warning("Carrier down: {$carrier->name}", [
                    'carrier_id' => $carrier->id,
                    'old_state' => $oldState,
                    'new_state' => $newState,
                ]);
            }

            // Carrier recovered
            if (in_array($oldState, ['inactive', 'probing', 'disabled']) && $newState === 'active') {
                // Create alert for carrier recovered
                Alert::create([
                    'type' => 'carrier_recovered',
                    'severity' => 'info',
                    'source_type' => 'carrier',
                    'source_id' => $carrier->id,
                    'source_name' => $carrier->name,
                    'title' => "Carrier recovered: {$carrier->name}",
                    'message' => "Carrier {$carrier->name} ({$carrier->host}:{$carrier->port}) is now active again.",
                    'metadata' => [
                        'host' => $carrier->host,
                        'port' => $carrier->port,
                        'old_state' => $oldState,
                    ],
                ]);

                // Also trigger webhook
                $this->webhookService->carrierRecovered($carrier);

                Log::info("Carrier recovered: {$carrier->name}", [
                    'carrier_id' => $carrier->id,
                    'old_state' => $oldState,
                ]);
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

            // Sync firewall rules (carrier IPs need to be allowed on SIP port)
            SyncFirewallJob::dispatchDebounced("carrier_{$action}:{$carrier->name}");

        } catch (\Exception $e) {
            Log::error("Failed to sync Kamailio dispatcher after carrier {$action}", [
                'carrier_id' => $carrier->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
