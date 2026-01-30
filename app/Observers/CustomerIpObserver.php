<?php

namespace App\Observers;

use App\Models\CustomerIp;
use App\Models\KamailioAddress;
use Illuminate\Support\Facades\Log;

class CustomerIpObserver
{
    /**
     * Handle the CustomerIp "created" event.
     */
    public function created(CustomerIp $customerIp): void
    {
        $this->syncToKamailio($customerIp, 'created');
    }

    /**
     * Handle the CustomerIp "updated" event.
     */
    public function updated(CustomerIp $customerIp): void
    {
        $this->syncToKamailio($customerIp, 'updated');
    }

    /**
     * Handle the CustomerIp "deleted" event.
     */
    public function deleted(CustomerIp $customerIp): void
    {
        $this->syncToKamailio($customerIp, 'deleted');
    }

    /**
     * Sincroniza la IP con Kamailio
     */
    protected function syncToKamailio(CustomerIp $customerIp, string $action): void
    {
        try {
            // Sincronizar todas las IPs (más seguro que sincronizar una sola)
            $result = KamailioAddress::syncAndReload();

            Log::info("Kamailio address sync after IP {$action}", [
                'customer_ip_id' => $customerIp->id,
                'ip_address' => $customerIp->ip_address,
                'customer_id' => $customerIp->customer_id,
                'synced_count' => $result['synced'],
                'reloaded' => $result['reloaded'],
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to sync Kamailio address after IP {$action}", [
                'customer_ip_id' => $customerIp->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
