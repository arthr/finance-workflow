<?php

namespace App\Domain\Webhook\Services;

use App\Domain\Webhook\Models\Webhook;
use App\Domain\Webhook\Models\WebhookLog;
use App\Domain\Webhook\Jobs\SendWebhookNotificationJob;
use Illuminate\Support\Facades\Log;

class WebhookService
{
    public function findWebhooksForEvent(string $eventName, ?int $workflowId = null): array
    {
        $query = Webhook::where('is_active', true)
            ->where(function ($query) use ($eventName) {
                $query->whereJsonContains('events', $eventName)
                    ->orWhereJsonContains('events', '*');
            });

        if ($workflowId) {
            $query->where(function ($query) use ($workflowId) {
                $query->where('workflow_id', $workflowId)
                    ->orWhereNull('workflow_id');
            });
        }

        return $query->get()->toArray();
    }

    public function dispatchWebhooks(string $eventName, array $payload, ?int $workflowId = null): void
    {
        $webhooks = $this->findWebhooksForEvent($eventName, $workflowId);

        foreach ($webhooks as $webhook) {
            SendWebhookNotificationJob::dispatch($webhook, $eventName, $payload);
            Log::info('Webhook dispatched', [
                'webhook_id' => $webhook['id'],
                'event' => $eventName
            ]);
        }
    }

    public function createSignature(array $payload, string $secret): string
    {
        $payloadJson = json_encode($payload);
        return hash_hmac('sha256', $payloadJson, $secret);
    }
}
