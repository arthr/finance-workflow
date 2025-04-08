<?php

namespace App\Domain\Webhook\Jobs;

use App\Domain\Webhook\Models\Webhook;
use App\Domain\Webhook\Models\WebhookLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendWebhookNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $webhook;
    protected $eventName;
    protected $payload;

    public function __construct(array $webhook, string $eventName, array $payload)
    {
        $this->webhook = $webhook;
        $this->eventName = $eventName;
        $this->payload = $payload;
        $this->queue = 'webhooks';
    }

    public function handle()
    {
        $webhook = Webhook::find($this->webhook['id']);

        if (!$webhook || !$webhook->is_active) {
            return;
        }

        $headers = $webhook->headers ?? [];
        $secret = $webhook->secret;

        if ($secret) {
            $signature = hash_hmac('sha256', json_encode($this->payload), $secret);
            $headers['X-Webhook-Signature'] = $signature;
        }

        $headers['Content-Type'] = 'application/json';
        $headers['X-Webhook-Event'] = $this->eventName;

        try {
            $response = Http::withHeaders($headers)
                ->timeout(30)
                ->post($webhook->url, [
                    'event' => $this->eventName,
                    'data' => $this->payload,
                    'timestamp' => now()->toIso8601String(),
                ]);

            $success = $response->successful();
            $statusCode = $response->status();

            // Registrar log da tentativa
            WebhookLog::create([
                'webhook_id' => $webhook->id,
                'event_name' => $this->eventName,
                'payload' => $this->payload,
                'status_code' => $statusCode,
                'response' => $response->body(),
                'success' => $success,
                'attempt' => $this->attempts()
            ]);

            if (!$success && $this->attempts() < $webhook->max_retries) {
                $this->release(
                    now()->addSeconds(30 * (2 ** $this->attempts()))
                );
            }

            Log::info('Webhook notification sent', [
                'webhook_id' => $webhook->id,
                'event' => $this->eventName,
                'success' => $success,
                'status_code' => $statusCode
            ]);
        } catch (\Exception $e) {
            WebhookLog::create([
                'webhook_id' => $webhook->id,
                'event_name' => $this->eventName,
                'payload' => $this->payload,
                'status_code' => 0,
                'response' => $e->getMessage(),
                'success' => false,
                'attempt' => $this->attempts()
            ]);

            if ($this->attempts() < $webhook->max_retries) {
                $this->release(
                    now()->addSeconds(30 * (2 ** $this->attempts()))
                );
            }

            Log::error('Webhook notification failed', [
                'webhook_id' => $webhook->id,
                'event' => $this->eventName,
                'error' => $e->getMessage()
            ]);
        }
    }
}
