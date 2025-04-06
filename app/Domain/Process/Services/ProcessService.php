<?php

namespace App\Domain\Process\Services;

use App\Domain\Process\Models\Process;
use App\Domain\Workflow\Models\Workflow;
use App\Domain\Workflow\Models\WorkflowStage;
use Illuminate\Support\Facades\DB;
use App\Domain\Process\Events\ProcessCreated;
use App\Domain\Process\Events\ProcessStageChanged;
use Illuminate\Support\Facades\Auth;

class ProcessService
{
    public function createProcess(array $data)
    {
        return DB::transaction(function () use ($data) {
            $workflow = Workflow::findOrFail($data['workflow_id']);
            $firstStage = $workflow->stages()->orderBy('order')->first();

            if (!$firstStage) {
                throw new \Exception('Workflow has no stages');
            }

            $process = Process::create([
                'workflow_id' => $workflow->id,
                'reference_type' => $data['reference_type'] ?? null,
                'reference_id' => $data['reference_id'] ?? null,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'current_stage_id' => $firstStage->id,
                'status' => 'active',
                'data' => $data['data'] ?? null,
                'created_by' => Auth::id(),
                'assigned_to' => $data['assigned_to'] ?? null,
            ]);

            $process->histories()->create([
                'to_stage_id' => $firstStage->id,
                'action' => 'process_created',
                'comments' => $data['comments'] ?? null,
                'performed_by' => Auth::id(),
            ]);

            event(new ProcessCreated($process));

            return $process;
        });
    }

    public function moveToNextStage(Process $process, array $data)
    {
        return DB::transaction(function () use ($process, $data) {
            $currentStage = $process->currentStage;
            $transition = $currentStage->outgoingTransitions()
                ->where('to_stage_id', $data['to_stage_id'])
                ->first();

            if (!$transition) {
                throw new \Exception('Invalid transition');
            }

            $fromStageId = $process->current_stage_id;
            $process->current_stage_id = $data['to_stage_id'];
            $process->assigned_to = $data['assigned_to'] ?? $process->assigned_to;
            $process->save();

            $process->histories()->create([
                'from_stage_id' => $fromStageId,
                'to_stage_id' => $data['to_stage_id'],
                'action' => 'stage_changed',
                'comments' => $data['comments'] ?? null,
                'performed_by' => Auth::id(),
            ]);

            event(new ProcessStageChanged($process, $fromStageId));

            return $process;
        });
    }
}
