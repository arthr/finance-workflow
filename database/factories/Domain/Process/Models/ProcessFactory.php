<?php

namespace Database\Factories\Domain\Process\Models;

use App\Domain\Process\Models\Process;
use App\Domain\Workflow\Models\Workflow;
use App\Domain\Workflow\Models\WorkflowStage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProcessFactory extends Factory
{
    protected $model = Process::class;

    public function definition()
    {
        return [
            'workflow_id' => Workflow::factory(),
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'current_stage_id' => WorkflowStage::factory(),
            'status' => 'active',
            'data' => [
                'key1' => $this->faker->word,
                'key2' => $this->faker->randomNumber(),
                'status' => $this->faker->randomElement(['new', 'pending', 'approved', 'rejected']),
            ],
            'created_by' => User::factory(),
            'assigned_to' => null,
        ];
    }

    public function completed()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'completed',
            ];
        });
    }

    public function cancelled()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'cancelled',
            ];
        });
    }
}
