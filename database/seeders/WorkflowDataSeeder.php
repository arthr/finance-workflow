<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domain\Workflow\Models\Workflow;
use App\Domain\Workflow\Models\WorkflowStage;
use App\Domain\Workflow\Models\WorkflowTransition;

class WorkflowDataSeeder extends Seeder
{
    public function run()
    {
        $workflow = Workflow::firstOrCreate([
            'name' => 'Cadastro',
            'description' => 'Fluxo exemplar de Cadastro de Cliente',
            'is_active' => true,
            'created_by' => 1,
        ]);

        $stages = [
            ['name' => 'Coleta de Dados', 'description' => 'Obter dados do cliente.', 'order' => 0, 'type' => 'manual'],
            ['name' => 'Validar Dados', 'description' => 'Verificar inconsistências.', 'order' => 1, 'type' => 'manual'],
            ['name' => 'Verificar Duplicidade', 'description' => 'Checar duplicidade.', 'order' => 2, 'type' => 'automatic'],
            ['name' => 'Cadastro no Sistema', 'description' => 'Inserir dados validados.', 'order' => 3, 'type' => 'manual'],
            ['name' => 'Notifica Crédito', 'description' => 'Notificar crédito.', 'order' => 4, 'type' => 'automatic'],
        ];

        $stageIds = [];
        foreach ($stages as $stage) {
            $stageIds[] = WorkflowStage::firstOrCreate(array_merge($stage, ['workflow_id' => $workflow->id]))->id;
        }

        $transitions = [
            ['from_stage_id' => $stageIds[0], 'to_stage_id' => $stageIds[1], 'condition' => json_encode(['field' => 'status', 'value' => 'concluido'])],
            ['from_stage_id' => $stageIds[1], 'to_stage_id' => $stageIds[2], 'condition' => json_encode(['field' => null, 'value' => null])],
            ['from_stage_id' => $stageIds[2], 'to_stage_id' => $stageIds[3], 'condition' => json_encode(['field' => 'status', 'value' => 'validado'])],
            ['from_stage_id' => $stageIds[3], 'to_stage_id' => $stageIds[4], 'condition' => json_encode(['field' => null, 'value' => null])],
        ];

        foreach ($transitions as $transition) {
            WorkflowTransition::firstOrCreate(array_merge($transition, ['workflow_id' => $workflow->id]));
        }
    }
}
