<?php

namespace App\Domain\Process\Events;

use App\Domain\Process\Models\Process;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessStageChanged
{
    use Dispatchable, SerializesModels;

    public $process;
    public $fromStageId;

    /**
     * Create a new event instance.
     */
    public function __construct(Process $process, $fromStageId)
    {
        $this->process = $process;
        $this->fromStageId = $fromStageId;
        Log::channel('process')->info('EVENT::Processo alterado de estágio', [
            'process_id' => $process->id,
            'from_stage_id' => $fromStageId,
            'to_stage_id' => $process->current_stage_id,
            'workflow_id' => $process->workflow_id,
        ]);
    }
}
