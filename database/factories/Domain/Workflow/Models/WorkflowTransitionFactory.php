<?php

namespace Database\Factories\Domain\Workflow\Models;

use App\Domain\Workflow\Models\WorkflowTransition;
use App\Domain\Workflow\Models\Workflow;
use App\Domain\Workflow\Models\WorkflowStage;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkflowTransitionFactory extends Factory
{
    protected $model = WorkflowTransition::class;

    public function definition()
    {
        return [
            'workflow_id' => Workflow::factory(),
            'from_stage_id' => WorkflowStage::factory(),
            'to_stage_id' => WorkflowStage::factory(),
            'trigger_type' => 'manual',
            'condition' => [],
        ];
    }

    public function manual()
    {
        return $this->state(function (array $attributes) {
            return [
                'trigger_type' => 'manual',
                'condition' => [],
            ];
        });
    }

    public function automatic()
    {
        return $this->state(function (array $attributes) {
            return [
                'trigger_type' => 'automatic',
                'condition' => [
                    'field' => 'status',
                    'operator' => 'equals',
                    'value' => 'approved',
                ],
            ];
        });
    }

    public function scheduled()
    {
        return $this->state(function (array $attributes) {
            return [
                'trigger_type' => 'scheduled',
                'condition' => [
                    'duration' => 24,
                    'unit' => 'hours',
                ],
            ];
        });
    }
}
