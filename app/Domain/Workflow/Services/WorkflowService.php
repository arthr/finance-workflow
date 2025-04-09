<?php

namespace App\Domain\Workflow\Services;

use App\Domain\Workflow\Models\Workflow;
use App\Domain\Workflow\Models\WorkflowStage;
use App\Domain\Workflow\Models\WorkflowTransition;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
        // 1. Validar se os estágios pertencem ao workflow
        $fromStage = $workflow->stages()->find($transitionData['from_stage_id']);
        $toStage = $workflow->stages()->find($transitionData['to_stage_id']);

        if (!$fromStage || !$toStage) {
            throw new \InvalidArgumentException('Os estágios selecionados não pertencem a este workflow.');
        }

        // 2. Impedir que um estágio transite para si mesmo
        if ($fromStage->id === $toStage->id) {
            throw new \InvalidArgumentException('Um estágio não pode ter uma transição para si mesmo.');
        }

        // 3. Verificar se já existe uma transição com os mesmos estágios
        $existingTransition = $workflow->transitions()
            ->where('from_stage_id', $fromStage->id)
            ->where('to_stage_id', $toStage->id)
            ->first();

        if ($existingTransition) {
            throw new \InvalidArgumentException('Já existe uma transição entre estes estágios.');
        }

        // 4. Validar tipo de transição e condições
        $triggerType = $transitionData['trigger_type'] ?? 'manual';
        $condition = $transitionData['condition'] ?? null;

        $processedCondition = $this->validateTransitionConditions($triggerType, $condition, $fromStage, $toStage);

        // 5. Criar a transição
        return $workflow->transitions()->create([
            'from_stage_id' => $fromStage->id,
            'to_stage_id' => $toStage->id,
            'condition' => $processedCondition,
            'trigger_type' => $triggerType,
        ]);
    }

    /**
     * Validar se as condições são válidas para o tipo de transição
     * @return array Retorna as condições possivelmente modificadas
     */
    private function validateTransitionConditions($triggerType, $condition, $fromStage, $toStage)
    {
        // Inicialize as condições como array vazio se for null
        $processedCondition = is_array($condition) ? $condition : [];

        switch ($triggerType) {
            case 'manual':
                // Condições opcionais para transições manuais
                if (isset($processedCondition['permission']) && empty($processedCondition['permission'])) {
                    // Se a permissão estiver definida mas vazia, remova-a
                    unset($processedCondition['permission']);
                }
                break;

            case 'automatic':
                // Validar condições para transições automáticas
                if (empty($processedCondition) || !isset($processedCondition['field']) || !isset($processedCondition['operator']) || !isset($processedCondition['value'])) {
                    throw new \InvalidArgumentException('Transições automáticas requerem condições com campo, operador e valor.');
                }

                // Validar operador
                $validOperators = ['equals', 'not_equals', 'greater_than', 'less_than', 'contains'];
                if (!in_array($processedCondition['operator'], $validOperators)) {
                    throw new \InvalidArgumentException('Operador inválido para condição automática.');
                }
                break;

            case 'scheduled':
                // Validar condições para transições agendadas
                if (empty($processedCondition) || !isset($processedCondition['duration']) || !isset($processedCondition['unit'])) {
                    throw new \InvalidArgumentException('Transições agendadas requerem duração e unidade de tempo.');
                }

                // Validar duração
                if (!is_numeric($processedCondition['duration']) || $processedCondition['duration'] <= 0) {
                    throw new \InvalidArgumentException('A duração deve ser um número positivo.');
                }

                // Validar unidade
                $validUnits = ['minutes', 'hours', 'days', 'weeks'];
                if (!in_array($processedCondition['unit'], $validUnits)) {
                    throw new \InvalidArgumentException('Unidade de tempo inválida.');
                }
                break;

            default:
                throw new \InvalidArgumentException('Tipo de gatilho inválido.');
        }

        // Validações adicionais baseadas no tipo de estágio de origem
        if ($fromStage->type === 'automatic' && $triggerType !== 'automatic') {
            throw new \InvalidArgumentException('Estágios automáticos devem ter transições automáticas.');
        }

        // Validações relacionadas à ordem dos estágios
        $this->validateStageOrder($fromStage, $toStage);

        return $processedCondition;
    }

    /**
     * Validar a ordem dos estágios para evitar fluxos inválidos
     */
    private function validateStageOrder($fromStage, $toStage)
    {
        // Esta é uma implementação básica que pode ser expandida conforme necessário

        // Verifica se existem processos ativos e se esta transição pode invalidar fluxos existentes
        $activeProcessesCount = $fromStage->workflow->processes()
            ->where('current_stage_id', $fromStage->id)
            ->where('status', 'active')
            ->count();

        // Se houver processos ativos neste estágio, registre um log de aviso
        if ($activeProcessesCount > 0) {
            Log::channel('workflow')->warning('Nova transição criada que afeta processos ativos', [
                'from_stage_id' => $fromStage->id,
                'to_stage_id' => $toStage->id,
                'active_processes' => $activeProcessesCount,
                'workflow_id' => $fromStage->workflow_id,
                'created_by' => Auth::id(),
            ]);
        }

        return true;
    }
}
