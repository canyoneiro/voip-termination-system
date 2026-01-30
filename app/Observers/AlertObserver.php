<?php

namespace App\Observers;

use App\Jobs\SendAlertNotificationJob;
use App\Models\Alert;
use App\Services\WebhookService;

class AlertObserver
{
    protected WebhookService $webhookService;

    public function __construct(WebhookService $webhookService)
    {
        $this->webhookService = $webhookService;
    }

    public function created(Alert $alert): void
    {
        // Send webhooks
        $this->webhookService->alertCreated($alert);

        // Send email and Telegram notifications
        SendAlertNotificationJob::dispatch($alert);
    }
}
