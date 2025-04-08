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
            // 1. Verificar se o processo está ativo
            if ($process->status !== 'active') {
                throw new \InvalidArgumentException('Não é possível mover um processo que não está ativo.');
            }

            // 2. Obter o estágio atual e buscar a transição
            $currentStage = $process->currentStage;
            $transition = $currentStage->outgoingTransitions()
                ->where('to_stage_id', $data['to_stage_id'])
                ->first();

            // 3. Verificar se a transição existe
            if (!$transition) {
                throw new \InvalidArgumentException('Transição inválida. O estágio atual não possui uma transição para o estágio solicitado.');
            }

            // 4. Validar permissões com base no tipo de transição
            if ($transition->trigger_type === 'manual') {
                $this->validateManualTransition($transition, $data);
            } elseif ($transition->trigger_type === 'automatic') {
                $this->validateAutomaticTransition($transition, $process, $data);
            } elseif ($transition->trigger_type === 'scheduled') {
                $this->validateScheduledTransition($transition, $process, $data);
            }

            // 5. Processar a mudança de estágio
            $fromStageId = $process->current_stage_id;
            $process->current_stage_id = $data['to_stage_id'];
            $process->assigned_to = $data['assigned_to'] ?? $process->assigned_to;

            // 6. Verificar se é necessário atualizar o status do processo
            $toStage = WorkflowStage::find($data['to_stage_id']);
            $this->updateProcessStatusBasedOnStage($process, $toStage);

            $process->save();

            // 7. Registrar no histórico
            $process->histories()->create([
                'from_stage_id' => $fromStageId,
                'to_stage_id' => $data['to_stage_id'],
                'action' => 'stage_changed',
                'comments' => $data['comments'] ?? null,
                'performed_by' => Auth::id(),
            ]);

            // 8. Disparar evento
            event(new ProcessStageChanged($process, $fromStageId));

            // 9. Registrar log para análise
            \Illuminate\Support\Facades\Log::info('Processo movido para novo estágio', [
                'process_id' => $process->id,
                'workflow_id' => $process->workflow_id,
                'from_stage' => $fromStageId,
                'to_stage' => $data['to_stage_id'],
                'performed_by' => Auth::id(),
                'timestamp' => now()->toIso8601String(),
            ]);

            return $process;
        });
    }

    /**
     * Validar uma transição manual
     */
    private function validateManualTransition($transition, $data)
    {
        // Verificar permissões necessárias
        if (isset($transition->condition['permission']) && !empty($transition->condition['permission'])) {
            $requiredPermission = $transition->condition['permission'];
            if (!Auth::user()->can($requiredPermission)) {
                throw new \InvalidArgumentException("Você não possui a permissão necessária ({$requiredPermission}) para executar esta transição.");
            }
        }

        // Verificações adicionais específicas para transições manuais podem ser adicionadas aqui
        return true;
    }

    /**
     * Validar uma transição automática
     */
    private function validateAutomaticTransition($transition, $process, $data)
    {
        // As transições automáticas normalmente não são movidas manualmente,
        // mas podemos permitir isso para usuários com permissões especiais
        if (!Auth::user()->can('manage processes')) {
            throw new \InvalidArgumentException("Transições automáticas não podem ser executadas manualmente sem permissão especial.");
        }

        // Se tivermos dados do processo, podemos validar se a condição seria atendida
        if (isset($transition->condition) && !empty($transition->condition)) {
            $condition = json_decode($transition->condition, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \InvalidArgumentException("Erro ao decodificar a condição da transição automática.");
            }
            // Verificar se a condição é válida
            if (!isset($condition['field'], $condition['operator'], $condition['value'])) {
                throw new \InvalidArgumentException("Condição inválida para transição automática.");
            }
            $fieldName = $condition['field'];
            $operator = $condition['operator'];
            $compareValue = $condition['value'];

            // Se o campo estiver nos dados do processo
            if (isset($process->data) && is_array($process->data) && array_key_exists($fieldName, $process->data)) {
                $processValue = $process->data[$fieldName];

                // Avaliar a condição
                $conditionMet = $this->evaluateCondition($processValue, $operator, $compareValue);

                if (!$conditionMet) {
                    throw new \InvalidArgumentException("A condição para transição automática não foi atendida.");
                }
            }
        }

        return true;
    }

    /**
     * Validar uma transição agendada
     */
    private function validateScheduledTransition($transition, $process, $data)
    {
        // Verificar se o tempo mínimo foi atingido
        if (isset($transition->condition) && !empty($transition->condition)) {
            $duration = $transition->condition['duration'];
            $unit = $transition->condition['unit'];

            // Obter a data da última transição para este estágio
            $latestTransition = $process->histories()
                ->where('to_stage_id', $process->current_stage_id)
                ->orderBy('created_at', 'desc')
                ->first();

            if ($latestTransition) {
                $minimumDate = $this->calculateMinimumDate($latestTransition->created_at, $duration, $unit);
                $now = now();

                if ($now->lt($minimumDate)) {
                    $remainingTime = $now->diffForHumans($minimumDate, true);
                    throw new \InvalidArgumentException("Ainda não é possível executar esta transição. Tempo restante: {$remainingTime}.");
                }
            }
        }

        return true;
    }

    /**
     * Avaliar uma condição de transição
     */
    private function evaluateCondition($processValue, $operator, $compareValue)
    {
        switch ($operator) {
            case 'equals':
                return $processValue == $compareValue;
            case 'not_equals':
                return $processValue != $compareValue;
            case 'greater_than':
                return $processValue > $compareValue;
            case 'less_than':
                return $processValue < $compareValue;
            case 'contains':
                return is_string($processValue) &&
                    is_string($compareValue) &&
                    strpos($processValue, $compareValue) !== false;
            default:
                return false;
        }
    }

    /**
     * Calcular a data mínima para uma transição agendada
     */
    private function calculateMinimumDate($startDate, $duration, $unit)
    {
        switch ($unit) {
            case 'minutes':
                return $startDate->addMinutes($duration);
            case 'hours':
                return $startDate->addHours($duration);
            case 'days':
                return $startDate->addDays($duration);
            case 'weeks':
                return $startDate->addWeeks($duration);
            default:
                return $startDate;
        }
    }

    /**
     * Atualizar o status do processo com base no estágio
     */
    private function updateProcessStatusBasedOnStage($process, $toStage)
    {
        // Esta é uma implementação básica. Você pode personalizar com base nas necessidades do negócio.
        // Por exemplo, pode haver estágios específicos que representem o fim do processo.

        // Estágios finais geralmente não têm transições de saída
        $hasOutgoingTransitions = $toStage->outgoingTransitions()->exists();

        if (!$hasOutgoingTransitions) {
            // Se o estágio não tiver transições de saída, considere como um estágio final
            // e marque o processo como completado
            $process->status = 'completed';
        }

        // Outras regras podem ser adicionadas aqui
    }
}
