<?php

namespace App\Observers;

use App\Jobs\SyncFirewallJob;
use App\Models\CarrierIp;
use Illuminate\Support\Facades\Log;

class CarrierIpObserver
{
    /**
     * Handle the CarrierIp "created" event.
     */
    public function created(CarrierIp $carrierIp): void
    {
        $this->syncFirewall($carrierIp, 'created');
    }

    /**
     * Handle the CarrierIp "updated" event.
     */
    public function updated(CarrierIp $carrierIp): void
    {
        $this->syncFirewall($carrierIp, 'updated');
    }

    /**
     * Handle the CarrierIp "deleted" event.
     */
    public function deleted(CarrierIp $carrierIp): void
    {
        $this->syncFirewall($carrierIp, 'deleted');
    }

    /**
     * Sync firewall when carrier IP changes.
     */
    protected function syncFirewall(CarrierIp $carrierIp, string $action): void
    {
        Log::info("Carrier IP {$action}, scheduling firewall sync", [
            'carrier_ip_id' => $carrierIp->id,
            'ip_address' => $carrierIp->ip_address,
            'carrier_id' => $carrierIp->carrier_id,
        ]);

        SyncFirewallJob::dispatchDebounced("carrier_ip_{$action}:{$carrierIp->ip_address}");
    }
}
