<?php

namespace Database\Factories\Domain\Process\Models;

use App\Domain\Process\Models\ProcessHistory;
use App\Domain\Process\Models\Process;
use App\Domain\Workflow\Models\WorkflowStage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProcessHistoryFactory extends Factory
{
    protected $model = ProcessHistory::class;

    public function definition()
    {
        return [
            'process_id' => Process::factory(),
            'from_stage_id' => WorkflowStage::factory(),
            'to_stage_id' => WorkflowStage::factory(),
            'action' => $this->faker->randomElement(['process_created', 'stage_changed', 'data_updated', 'process_completed']),
            'comments' => $this->faker->sentence,
            'performed_by' => User::factory(),
        ];
    }

    public function creation()
    {
        return $this->state(function (array $attributes) {
            return [
                'from_stage_id' => null,
                'action' => 'process_created',
                'comments' => 'Processo criado',
            ];
        });
    }

    public function stageChange()
    {
        return $this->state(function (array $attributes) {
            return [
                'action' => 'stage_changed',
                'comments' => 'Estágio alterado',
            ];
        });
    }

    public function completion()
    {
        return $this->state(function (array $attributes) {
            return [
                'action' => 'process_completed',
                'comments' => 'Processo concluído',
            ];
        });
    }
}
