<?php

namespace Database\Factories\Domain\Workflow\Models;

use App\Domain\Workflow\Models\Workflow;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

class WorkflowFactory extends Factory
{
    protected $model = Workflow::class;

    public function definition()
    {
        return [
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph,
            'is_active' => true,
            'created_by' => User::factory(),
        ];
    }

    public function inactive()
    {
        return $this->state(function (array $attributes) {
            return [
                'is_active' => false,
            ];
        });
    }
}
