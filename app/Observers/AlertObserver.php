<?php

namespace App\Observers;

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
        $this->webhookService->alertCreated($alert);
    }
}
