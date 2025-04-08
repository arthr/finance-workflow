<?php

namespace App\Domain\Process\Listeners;

use App\Domain\Process\Events\ProcessCreated;
use App\Domain\Process\Events\ProcessStageChanged;
use App\Domain\Webhook\Services\WebhookService;

class SendProcessWebhooks
{
    protected $webhookService;

    public function __construct(WebhookService $webhookService)
    {
        $this->webhookService = $webhookService;
    }

    public function handleProcessCreated(ProcessCreated $event)
    {
        $process = $event->process;
        $this->webhookService->dispatchWebhooks(
            'process.created',
            $process->toArray(),
            $process->workflow_id
        );
    }

    public function handleProcessStageChanged(ProcessStageChanged $event)
    {
        $process = $event->process;
        $this->webhookService->dispatchWebhooks(
            'process.stage_changed',
            [
                'process' => $process->toArray(),
                'previous_stage' => null, // TODO: Implement logic to get the previous stage in ProcessStageChanged
                'current_stage' => $process->currentStage ? $process->currentStage->toArray() : null,
            ],
            $process->workflow_id
        );
    }

    public function subscribe($events)
    {
        $events->listen(
            ProcessCreated::class,
            [SendProcessWebhooks::class, 'handleProcessCreated']
        );

        $events->listen(
            ProcessStageChanged::class,
            [SendProcessWebhooks::class, 'handleProcessStageChanged']
        );
    }
}
