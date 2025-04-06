<?php

namespace App\Domain\Workflow\Services;

use App\Domain\Workflow\Models\Workflow;
use App\Domain\Workflow\Models\WorkflowStage;
use App\Domain\Workflow\Models\WorkflowTransition;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class WorkflowService
{
    public function createWorkflow(array $data)
    {
        return DB::transaction(function () use ($data) {
            $workflow = Workflow::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'is_active' => $data['is_active'] ?? true,
                'created_by' => Auth::id(),
            ]);

            if (isset($data['stages']) && is_array($data['stages'])) {
                foreach ($data['stages'] as $order => $stageData) {
                    $stage = $workflow->stages()->create([
                        'name' => $stageData['name'],
                        'description' => $stageData['description'] ?? null,
                        'order' => $order,
                        'type' => $stageData['type'] ?? 'manual',
                        'config' => $stageData['config'] ?? null,
                    ]);
                }
            }

            return $workflow;
        });
    }

    public function updateWorkflow(Workflow $workflow, array $data)
    {
        return DB::transaction(function () use ($workflow, $data) {
            $workflow->update([
                'name' => $data['name'],
                'description' => $data['description'] ?? $workflow->description,
                'is_active' => $data['is_active'] ?? $workflow->is_active,
            ]);

            return $workflow;
        });
    }

    public function addStage(Workflow $workflow, array $stageData)
    {
        $lastOrder = $workflow->stages()->max('order') ?? -1;

        return $workflow->stages()->create([
            'name' => $stageData['name'],
            'description' => $stageData['description'] ?? null,
            'order' => $lastOrder + 1,
            'type' => $stageData['type'] ?? 'manual',
            'config' => $stageData['config'] ?? null,
        ]);
    }

    public function addTransition(Workflow $workflow, array $transitionData)
    {
        return $workflow->transitions()->create([
            'from_stage_id' => $transitionData['from_stage_id'],
            'to_stage_id' => $transitionData['to_stage_id'],
            'condition' => $transitionData['condition'] ?? null,
            'trigger_type' => $transitionData['trigger_type'] ?? 'manual',
        ]);
    }
}
