<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\WebhookEndpoint;
use App\Models\Customer;
use App\Jobs\SendWebhookJob;
use App\Services\WebhookService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WebhookController extends Controller
{
    public function index()
    {
        $webhooks = WebhookEndpoint::with('customer')
            ->withCount('deliveries')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('webhooks.index', compact('webhooks'));
    }

    public function create()
    {
        $customers = Customer::orderBy('name')->get(['id', 'name']);
        $availableEvents = WebhookService::EVENTS;

        return view('webhooks.create', compact('customers', 'availableEvents'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'url' => 'required|url|max:500',
            'customer_id' => 'nullable|exists:customers,id',
            'events' => 'required|array|min:1',
            'events.*' => 'string|in:' . implode(',', WebhookService::EVENTS),
        ]);

        $secret = Str::random(64);

        $webhook = WebhookEndpoint::create([
            'uuid' => Str::uuid(),
            'url' => $validated['url'],
            'customer_id' => $validated['customer_id'] ?: null,
            'secret' => $secret,
            'events' => $validated['events'],
            'active' => true,
        ]);

        return redirect()
            ->route('webhooks.show', $webhook)
            ->with('success', 'Webhook created successfully')
            ->with('new_secret', $secret);
    }

    public function show(WebhookEndpoint $webhook)
    {
        $webhook->load('customer');

        $recentDeliveries = $webhook->deliveries()
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return view('webhooks.show', compact('webhook', 'recentDeliveries'));
    }

    public function edit(WebhookEndpoint $webhook)
    {
        $customers = Customer::orderBy('name')->get(['id', 'name']);
        $availableEvents = WebhookService::EVENTS;

        return view('webhooks.edit', compact('webhook', 'customers', 'availableEvents'));
    }

    public function update(Request $request, WebhookEndpoint $webhook)
    {
        $validated = $request->validate([
            'url' => 'required|url|max:500',
            'customer_id' => 'nullable|exists:customers,id',
            'events' => 'required|array|min:1',
            'events.*' => 'string|in:' . implode(',', WebhookService::EVENTS),
            'active' => 'boolean',
        ]);

        $webhook->update([
            'url' => $validated['url'],
            'customer_id' => $validated['customer_id'] ?: null,
            'events' => $validated['events'],
            'active' => $request->has('active'),
        ]);

        return redirect()
            ->route('webhooks.show', $webhook)
            ->with('success', 'Webhook updated successfully');
    }

    public function destroy(WebhookEndpoint $webhook)
    {
        $webhook->delete();

        return redirect()
            ->route('webhooks.index')
            ->with('success', 'Webhook deleted successfully');
    }

    public function regenerateSecret(WebhookEndpoint $webhook)
    {
        $newSecret = Str::random(64);
        $webhook->update(['secret' => $newSecret]);

        return redirect()
            ->route('webhooks.show', $webhook)
            ->with('success', 'Secret regenerated successfully')
            ->with('new_secret', $newSecret);
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

        return back()->with('success', 'Test webhook queued for delivery');
    }

    public function deliveries(WebhookEndpoint $webhook)
    {
        $deliveries = $webhook->deliveries()
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return view('webhooks.deliveries', compact('webhook', 'deliveries'));
    }
}
