<?php

namespace App\Domain\Process\Events;

use App\Domain\Process\Models\Process;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProcessCreated
{
    use Dispatchable, SerializesModels;

    public $process;

    /**
     * Create a new event instance.
     */
    public function __construct(Process $process)
    {
        $this->process = $process;
    }
}
