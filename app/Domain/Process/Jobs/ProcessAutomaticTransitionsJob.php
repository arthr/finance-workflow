<?php

namespace App\Domain\Process\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Domain\Process\Models\Process;
use App\Domain\Workflow\Models\WorkflowTransition;
use App\Domain\Process\Services\ProcessService;
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
        $processService = new ProcessService();
        $processes = Process::with(['currentStage.outgoingTransitions', 'workflow'])
            ->where('status', 'active')
            ->get();

        Log::info('Processando transições automáticas', ['total_processes' => $processes->count()]);

        foreach ($processes as $process) {
            $automaticTransitions = $process->currentStage->outgoingTransitions()
                ->where('trigger_type', 'automatic')
                ->get();

            foreach ($automaticTransitions as $transition) {
                try {
                    $this->processAutomaticTransition($processService, $process, $transition);
                } catch (\Exception $e) {
                    Log::error('Erro ao processar transição automática', [
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
        // Verifica se as condições automáticas são atendidas
        if (!$transition->condition || empty($transition->condition)) {
            return;
        }

        $condition = json_decode($transition->condition, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('Erro ao decodificar condição JSON', [
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
