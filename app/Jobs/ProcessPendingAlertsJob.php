<?php

namespace App\Jobs;

use App\Models\Alert;
use App\Models\SystemSetting;
use App\Services\WebhookService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Process alerts that were inserted directly into DB (bypassing Eloquent)
 * This catches alerts from Kamailio that don't trigger AlertObserver
 */
class ProcessPendingAlertsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 60;

    public function handle(WebhookService $webhookService): void
    {
        // Find alerts created in last 5 minutes that haven't been notified
        $pendingAlerts = Alert::where('created_at', '>=', now()->subMinutes(5))
            ->where('notified_email', false)
            ->where('notified_telegram', false)
            ->orderBy('created_at', 'asc')
            ->limit(50)
            ->get();

        if ($pendingAlerts->isEmpty()) {
            return;
        }

        Log::info("Processing {$pendingAlerts->count()} pending alerts from Kamailio");

        foreach ($pendingAlerts as $alert) {
            try {
                // Send webhooks
                $webhookService->alertCreated($alert);

                // Dispatch notification job
                SendAlertNotificationJob::dispatch($alert);

                Log::debug("Dispatched notification for alert {$alert->id}");

            } catch (\Exception $e) {
                Log::error("Failed to process pending alert {$alert->id}: " . $e->getMessage());
            }
        }
    }
}
