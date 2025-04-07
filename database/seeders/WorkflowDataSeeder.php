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
            ['name' => 'Notificar Solicitante', 'description' => 'Notificar situação do cadastro ao solicitante.', 'order' => 5, 'type' => 'conditional', 'config' => '{"delay":"1","field":"status","value":"duplicado","action":"notify","operator":"equals"}'],
            ['name' => 'Descartar Cadastro', 'description' => 'Excluir dados coletados do cadastro.', 'order' => 6, 'type' => 'automatic', 'config' => '{"delay":"15","field":null,"value":null,"action":"notify","operator":"equals"}'],
        ];

        $stageIds = [];
        foreach ($stages as $stage) {
            $stageIds[] = WorkflowStage::firstOrCreate(array_merge($stage, ['workflow_id' => $workflow->id]))->id;
        }

        $transitions = [
            [
                'from_stage_id' => $stageIds[0],
                'to_stage_id' => $stageIds[1],
                'condition' => '{"unit":"minutes","field":null,"value":null,"duration":"1","operator":"equals","permission":"approve_request"}',
                'trigger_type' => 'manual',
            ],
            [
                'from_stage_id' => $stageIds[1],
                'to_stage_id' => $stageIds[2],
                'condition' => '{"unit":"minutes","field":"status","value":"validado","duration":"1","operator":"equals","permission":null}',
                'trigger_type' => 'automatic',
            ],
            [
                'from_stage_id' => $stageIds[2],
                'to_stage_id' => $stageIds[3],
                'condition' => '{"unit":"minutes","field":"status","value":"verificado","duration":"1","operator":"equals","permission":null}',
                'trigger_type' => 'automatic',
            ],
            [
                'from_stage_id' => $stageIds[3],
                'to_stage_id' => $stageIds[4],
                'condition' => '{"unit":"minutes","field":"status","value":"concluido","duration":"1","operator":"equals","permission":null}',
                'trigger_type' => 'automatic',
            ],
            [
                'from_stage_id' => $stageIds[2],
                'to_stage_id' => $stageIds[5],
                'condition' => '{"unit":"minutes","field":"status","value":"duplicado","duration":"1","operator":"equals","permission":null}',
                'trigger_type' => 'automatic',
            ],
            [
                'from_stage_id' => $stageIds[5],
                'to_stage_id' => $stageIds[6],
                'condition' => '{"unit":"days","field":null,"value":null,"duration":"2","operator":"equals","permission":null,"business_days":"1"}',
                'trigger_type' => 'scheduled',
            ],
        ];

        foreach ($transitions as $transition) {
            WorkflowTransition::firstOrCreate(array_merge($transition, ['workflow_id' => $workflow->id]));
        }
    }
}
