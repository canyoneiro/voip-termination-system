<?php

namespace App\Observers;

use App\Jobs\SyncFirewallJob;
use App\Models\Customer;
use Illuminate\Support\Facades\Log;

class CustomerObserver
{
    /**
     * Handle the Customer "updated" event.
     */
    public function updated(Customer $customer): void
    {
        // If active status changed, sync firewall
        if ($customer->isDirty('active')) {
            $oldActive = $customer->getOriginal('active');
            $newActive = $customer->active;

            Log::info("Customer active status changed, scheduling firewall sync", [
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'old_active' => $oldActive,
                'new_active' => $newActive,
            ]);

            SyncFirewallJob::dispatchDebounced("customer_status_change:{$customer->name}");
        }
    }

    /**
     * Handle the Customer "deleted" event.
     */
    public function deleted(Customer $customer): void
    {
        Log::info("Customer deleted, scheduling firewall sync", [
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
        ]);

        SyncFirewallJob::dispatchDebounced("customer_deleted:{$customer->name}");
    }
}
