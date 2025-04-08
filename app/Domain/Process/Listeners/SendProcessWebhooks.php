<?php

namespace App\Domain\Process\Listeners;

use App\Domain\Process\Events\ProcessCreatedEvent;
use App\Domain\Process\Events\ProcessStageChangedEvent;
use App\Domain\Webhook\Services\WebhookService;

class SendProcessWebhooks
{
    protected $webhookService;

    public function __construct(WebhookService $webhookService)
    {
        $this->webhookService = $webhookService;
    }

    public function handleProcessCreated(ProcessCreatedEvent $event)
    {
        $process = $event->process;
        $this->webhookService->dispatchWebhooks(
            'process.created',
            $process->toArray(),
            $process->workflow_id
        );
    }

    public function handleProcessStageChanged(ProcessStageChangedEvent $event)
    {
        $process = $event->process;
        $this->webhookService->dispatchWebhooks(
            'process.stage_changed',
            [
                'process' => $process->toArray(),
                'previous_stage' => $event->previousStage,
                'current_stage' => $event->currentStage,
            ],
            $process->workflow_id
        );
    }

    public function subscribe($events)
    {
        $events->listen(
            ProcessCreatedEvent::class,
            [SendProcessWebhooks::class, 'handleProcessCreated']
        );

        $events->listen(
            ProcessStageChangedEvent::class,
            [SendProcessWebhooks::class, 'handleProcessStageChanged']
        );
    }
}
