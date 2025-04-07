<?php

namespace Database\Factories\Domain\Workflow\Models;

use App\Domain\Workflow\Models\WorkflowStage;
use App\Domain\Workflow\Models\Workflow;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkflowStageFactory extends Factory
{
    protected $model = WorkflowStage::class;

    public function definition()
    {
        static $order = 0;

        return [
            'workflow_id' => Workflow::factory(),
            'name' => $this->faker->word . ' Stage',
            'description' => $this->faker->sentence,
            'order' => $order++,
            'type' => $this->faker->randomElement(['manual', 'automatic', 'conditional']),
            'config' => [],
        ];
    }

    public function manual()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'manual',
            ];
        });
    }

    public function automatic()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'automatic',
                'config' => [
                    'condition' => 'status = approved',
                ],
            ];
        });
    }

    public function initial()
    {
        return $this->state(function (array $attributes) {
            return [
                'order' => 0,
                'is_initial' => true,
            ];
        });
    }
}
