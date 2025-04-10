<?php

namespace App\Domain\Process\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Domain\Process\Models\Process;
use App\Domain\Workflow\Models\WorkflowTransition;
use App\Domain\Process\Services\ProcessService;
use App\Domain\Workflow\Models\WorkflowStage;
use Illuminate\Support\Facades\Log;

class ProcessAutomaticTransitionsJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $log = Log::channel('process');
        $processService = new ProcessService();

        // Busca IDs de estágios que possuem transições automáticas
        $stagesWithAutoTransitions = WorkflowStage::whereHas('outgoingTransitions', function ($query) {
            $query->where('trigger_type', 'automatic');
        })->pluck('id')->toArray();

        // Se não existirem estágios com transições automáticas, não há nada a fazer
        if (empty($stagesWithAutoTransitions)) {
            $log->info('Nenhum estágio com transições automáticas encontrado');
            return;
        }

        $log->info('Estágios com transições', [
            'estagios_com_transicoes' => $stagesWithAutoTransitions
        ]);

        // Busca apenas processos ativos que estão em estágios com transições automáticas
        // e que não estão em estágios finais (estágios finais não têm transições de saída)
        $processes = Process::with(['currentStage.outgoingTransitions' => function ($query) {
            $query->where('trigger_type', 'automatic');
        }, 'workflow'])
            ->where('status', 'active')
            ->whereIn('current_stage_id', $stagesWithAutoTransitions)
            ->get();

        $log->info('Processando transições automáticas', [
            'total_processes' => $processes->count(),
            'estagios_com_transicoes' => count($stagesWithAutoTransitions)
        ]);

        foreach ($processes as $process) {
            $automaticTransitions = $process->currentStage->outgoingTransitions;

            // Se não houver transições automáticas, pula esse processo
            if ($automaticTransitions->isEmpty()) {
                continue;
            }

            foreach ($automaticTransitions as $transition) {
                try {
                    $this->processAutomaticTransition($processService, $process, $transition);
                } catch (\Exception $e) {
                    $log->error('Erro ao processar transição automática', [
                        'process_id' => $process->id,
                        'transition_id' => $transition->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }
    }

    /**
     * Processa uma transição automática para um processo
     */
    private function processAutomaticTransition(ProcessService $processService, Process $process, WorkflowTransition $transition)
    {
        $log = Log::channel('process');
        // Verifica se as condições automáticas são atendidas
        if (!$transition->condition || empty($transition->condition)) {
            return;
        }

        $condition = is_string($transition->condition)
            ? json_decode($transition->condition, true)
            : $transition->condition;

        if (json_last_error() !== JSON_ERROR_NONE) {
            $log->error('Erro ao decodificar condição JSON', [
                'process_id' => $process->id,
                'transition_id' => $transition->id,
                'error' => json_last_error_msg()
            ]);
            return;
        }

        $fieldName = $condition['field'] ?? null;
        $operator = $condition['operator'] ?? null;
        $compareValue = $condition['value'] ?? null;

        if (!$fieldName || !$operator || !$compareValue) {
            return;
        }

        // Verifica se o campo está nos dados do processo
        if (!isset($process->data) || !is_array($process->data) || !array_key_exists($fieldName, $process->data)) {
            return;
        }

        $processValue = $process->data[$fieldName];
        $conditionMet = $this->evaluateCondition($processValue, $operator, $compareValue);

        if ($conditionMet) {
            $processService->moveToNextStage($process, [
                'to_stage_id' => $transition->to_stage_id,
                'comments' => 'Transição automática executada pelo sistema'
            ]);
        }
    }

    /**
     * Avalia se uma condição é atendida
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
}
