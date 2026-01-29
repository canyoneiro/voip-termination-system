<?php

namespace App\Jobs;

use App\Models\WebhookEndpoint;
use App\Models\WebhookDelivery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 300, 900]; // 1min, 5min, 15min

    protected WebhookEndpoint $webhook;
    protected string $event;
    protected array $payload;

    public function __construct(WebhookEndpoint $webhook, string $event, array $payload)
    {
        $this->webhook = $webhook;
        $this->event = $event;
        $this->payload = $payload;
        $this->onQueue('webhooks');
    }

    public function handle(): void
    {
        if (!$this->webhook->active) {
            Log::info("Webhook {$this->webhook->id} is inactive, skipping");
            return;
        }

        $timestamp = now()->timestamp;
        $body = json_encode($this->payload);
        $signature = $this->generateSignature($body, $timestamp);

        $delivery = WebhookDelivery::create([
            'webhook_id' => $this->webhook->id,
            'event' => $this->event,
            'payload' => $this->payload,
            'attempts' => $this->attempts(),
            'success' => false,
        ]);

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'X-Webhook-Event' => $this->event,
                    'X-Webhook-Timestamp' => $timestamp,
                    'X-Webhook-Signature' => $signature,
                    'X-Webhook-Id' => $this->webhook->uuid,
                    'User-Agent' => 'VoIP-Panel-Webhook/1.0',
                ])
                ->post($this->webhook->url, $this->payload);

            $delivery->update([
                'response_code' => $response->status(),
                'response_body' => substr($response->body(), 0, 5000),
                'success' => $response->successful(),
            ]);

            $this->webhook->update([
                'last_triggered_at' => now(),
                'last_status_code' => $response->status(),
                'failure_count' => $response->successful() ? 0 : $this->webhook->failure_count + 1,
            ]);

            if (!$response->successful()) {
                Log::warning("Webhook delivery failed", [
                    'webhook_id' => $this->webhook->id,
                    'url' => $this->webhook->url,
                    'status' => $response->status(),
                    'attempt' => $this->attempts(),
                ]);

                if ($this->attempts() < $this->tries) {
                    $this->release($this->backoff[$this->attempts() - 1] ?? 900);
                }
            } else {
                Log::info("Webhook delivered successfully", [
                    'webhook_id' => $this->webhook->id,
                    'event' => $this->event,
                ]);
            }

        } catch (\Exception $e) {
            $delivery->update([
                'response_body' => 'Connection error: ' . $e->getMessage(),
                'success' => false,
            ]);

            $this->webhook->update([
                'last_triggered_at' => now(),
                'failure_count' => $this->webhook->failure_count + 1,
            ]);

            Log::error("Webhook delivery exception", [
                'webhook_id' => $this->webhook->id,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            if ($this->attempts() < $this->tries) {
                $this->release($this->backoff[$this->attempts() - 1] ?? 900);
            }
        }
    }

    protected function generateSignature(string $body, int $timestamp): string
    {
        $payload = "{$timestamp}.{$body}";
        return 'sha256=' . hash_hmac('sha256', $payload, $this->webhook->secret);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("Webhook job failed permanently", [
            'webhook_id' => $this->webhook->id,
            'event' => $this->event,
            'error' => $exception->getMessage(),
        ]);

        $this->webhook->update([
            'failure_count' => $this->webhook->failure_count + 1,
        ]);
    }
}
