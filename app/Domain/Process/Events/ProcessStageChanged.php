<?php

namespace App\Domain\Process\Events;

use App\Domain\Process\Models\Process;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

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
    }
}
