<?php

namespace App\Services;

use App\Models\WebhookEndpoint;
use App\Models\Customer;
use App\Models\Carrier;
use App\Models\Cdr;
use App\Models\ActiveCall;
use App\Models\Alert;
use App\Jobs\SendWebhookJob;

class WebhookService
{
    /**
     * Available webhook events
     */
    public const EVENTS = [
        'call.started',
        'call.answered',
        'call.ended',
        'customer.minutes_warning',
        'customer.minutes_exhausted',
        'customer.channels_warning',
        'carrier.down',
        'carrier.recovered',
        'alert.created',
    ];

    /**
     * Trigger webhooks for a specific event
     */
    public function trigger(string $event, array $data, ?int $customerId = null): void
    {
        $query = WebhookEndpoint::where('active', true)
            ->whereJsonContains('events', $event);

        // If customer-specific, get webhooks for that customer OR global webhooks
        if ($customerId) {
            $query->where(function ($q) use ($customerId) {
                $q->where('customer_id', $customerId)
                    ->orWhereNull('customer_id');
            });
        } else {
            // Global event - only trigger global webhooks
            $query->whereNull('customer_id');
        }

        $webhooks = $query->get();

        foreach ($webhooks as $webhook) {
            $payload = [
                'event' => $event,
                'timestamp' => now()->toIso8601String(),
                'data' => $data,
            ];

            SendWebhookJob::dispatch($webhook, $event, $payload);
        }
    }

    /**
     * Call started event
     */
    public function callStarted(ActiveCall $call): void
    {
        $this->trigger('call.started', [
            'call_id' => $call->call_id,
            'customer_id' => $call->customer_id,
            'customer_name' => $call->customer->name ?? null,
            'caller' => $call->caller,
            'callee' => $call->callee,
            'source_ip' => $call->source_ip,
            'start_time' => $call->start_time->toIso8601String(),
        ], $call->customer_id);
    }

    /**
     * Call answered event
     */
    public function callAnswered(ActiveCall $call): void
    {
        $this->trigger('call.answered', [
            'call_id' => $call->call_id,
            'customer_id' => $call->customer_id,
            'customer_name' => $call->customer->name ?? null,
            'carrier_id' => $call->carrier_id,
            'carrier_name' => $call->carrier->name ?? null,
            'caller' => $call->caller,
            'callee' => $call->callee,
            'start_time' => $call->start_time->toIso8601String(),
            'answer_time' => $call->answer_time?->toIso8601String(),
        ], $call->customer_id);
    }

    /**
     * Call ended event (with CDR data)
     */
    public function callEnded(Cdr $cdr): void
    {
        $this->trigger('call.ended', [
            'uuid' => $cdr->uuid,
            'call_id' => $cdr->call_id,
            'customer_id' => $cdr->customer_id,
            'customer_name' => $cdr->customer->name ?? null,
            'carrier_id' => $cdr->carrier_id,
            'carrier_name' => $cdr->carrier->name ?? null,
            'caller' => $cdr->caller,
            'callee' => $cdr->callee,
            'start_time' => $cdr->start_time->toIso8601String(),
            'answer_time' => $cdr->answer_time?->toIso8601String(),
            'end_time' => $cdr->end_time?->toIso8601String(),
            'duration' => $cdr->duration,
            'billable_duration' => $cdr->billable_duration,
            'sip_code' => $cdr->sip_code,
            'sip_reason' => $cdr->sip_reason,
            'hangup_cause' => $cdr->hangup_cause,
            'pdd' => $cdr->pdd,
        ], $cdr->customer_id);
    }

    /**
     * Customer minutes warning (80% used)
     */
    public function customerMinutesWarning(Customer $customer, string $type, int $used, int $max): void
    {
        $this->trigger('customer.minutes_warning', [
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'type' => $type, // 'daily' or 'monthly'
            'used_minutes' => $used,
            'max_minutes' => $max,
            'percentage' => round(($used / $max) * 100, 1),
        ], $customer->id);
    }

    /**
     * Customer minutes exhausted (100% used)
     */
    public function customerMinutesExhausted(Customer $customer, string $type): void
    {
        $this->trigger('customer.minutes_exhausted', [
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'type' => $type,
        ], $customer->id);
    }

    /**
     * Customer channels warning (80% used)
     */
    public function customerChannelsWarning(Customer $customer, int $active, int $max): void
    {
        $this->trigger('customer.channels_warning', [
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'active_channels' => $active,
            'max_channels' => $max,
            'percentage' => round(($active / $max) * 100, 1),
        ], $customer->id);
    }

    /**
     * Carrier down event
     */
    public function carrierDown(Carrier $carrier, ?string $reason = null): void
    {
        $this->trigger('carrier.down', [
            'carrier_id' => $carrier->id,
            'carrier_name' => $carrier->name,
            'host' => $carrier->host,
            'port' => $carrier->port,
            'previous_state' => $carrier->getOriginal('state'),
            'reason' => $reason,
        ]);
    }

    /**
     * Carrier recovered event
     */
    public function carrierRecovered(Carrier $carrier): void
    {
        $this->trigger('carrier.recovered', [
            'carrier_id' => $carrier->id,
            'carrier_name' => $carrier->name,
            'host' => $carrier->host,
            'port' => $carrier->port,
        ]);
    }

    /**
     * Alert created event
     */
    public function alertCreated(Alert $alert): void
    {
        $this->trigger('alert.created', [
            'uuid' => $alert->uuid,
            'type' => $alert->type,
            'severity' => $alert->severity,
            'title' => $alert->title,
            'message' => $alert->message,
            'source_type' => $alert->source_type,
            'source_id' => $alert->source_id,
            'source_name' => $alert->source_name,
            'metadata' => $alert->metadata,
        ], $alert->source_type === 'customer' ? $alert->source_id : null);
    }
}
