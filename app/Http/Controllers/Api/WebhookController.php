<?php

namespace App\Http\Controllers\Api;

use App\Models\WebhookEndpoint;
use App\Models\WebhookDelivery;
use App\Jobs\SendWebhookJob;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WebhookController extends BaseApiController
{
    public function index(Request $request)
    {
        $query = WebhookEndpoint::query();

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filled('active')) {
            $query->where('active', $request->boolean('active'));
        }

        $webhooks = $query->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 20));

        return $this->paginated($webhooks);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'url' => 'required|url|max:500',
            'customer_id' => 'nullable|exists:customers,id',
            'events' => 'required|array|min:1',
            'events.*' => 'string|in:call.started,call.answered,call.ended,customer.minutes_warning,customer.minutes_exhausted,customer.channels_warning,carrier.down,carrier.recovered,alert.created',
            'active' => 'boolean',
        ]);

        $webhook = WebhookEndpoint::create([
            'uuid' => Str::uuid(),
            'url' => $validated['url'],
            'customer_id' => $validated['customer_id'] ?? null,
            'secret' => Str::random(64),
            'events' => $validated['events'],
            'active' => $validated['active'] ?? true,
        ]);

        return $this->success([
            'webhook' => $webhook,
            'secret' => $webhook->secret, // Only shown once
            'message' => 'Webhook created successfully. Save the secret - it won\'t be shown again.',
        ], [], 201);
    }

    public function show(WebhookEndpoint $webhook)
    {
        $webhook->load('customer');
        return $this->success($webhook);
    }

    public function update(Request $request, WebhookEndpoint $webhook)
    {
        $validated = $request->validate([
            'url' => 'sometimes|url|max:500',
            'events' => 'sometimes|array|min:1',
            'events.*' => 'string|in:call.started,call.answered,call.ended,customer.minutes_warning,customer.minutes_exhausted,customer.channels_warning,carrier.down,carrier.recovered,alert.created',
            'active' => 'sometimes|boolean',
        ]);

        $webhook->update($validated);

        return $this->success($webhook);
    }

    public function destroy(WebhookEndpoint $webhook)
    {
        $webhook->delete();
        return $this->success(['message' => 'Webhook deleted successfully']);
    }

    public function regenerateSecret(WebhookEndpoint $webhook)
    {
        $newSecret = Str::random(64);
        $webhook->update(['secret' => $newSecret]);

        return $this->success([
            'secret' => $newSecret,
            'message' => 'Secret regenerated. Save it - it won\'t be shown again.',
        ]);
    }

    public function test(WebhookEndpoint $webhook)
    {
        $testPayload = [
            'event' => 'webhook.test',
            'timestamp' => now()->toIso8601String(),
            'data' => [
                'message' => 'This is a test webhook delivery',
                'webhook_id' => $webhook->uuid,
            ],
        ];

        SendWebhookJob::dispatch($webhook, 'webhook.test', $testPayload);

        return $this->success(['message' => 'Test webhook queued for delivery']);
    }

    public function deliveries(WebhookEndpoint $webhook, Request $request)
    {
        $deliveries = $webhook->deliveries()
            ->orderBy('created_at', 'desc')
            ->paginate($request->input('per_page', 50));

        return $this->paginated($deliveries);
    }

    public function retryDelivery(WebhookDelivery $delivery)
    {
        $webhook = $delivery->webhook;

        if (!$webhook) {
            return $this->notFound('Webhook endpoint not found');
        }

        SendWebhookJob::dispatch($webhook, $delivery->event, $delivery->payload);

        return $this->success(['message' => 'Delivery retry queued']);
    }
}
