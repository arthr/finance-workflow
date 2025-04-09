<?php

namespace App\Domain\Process\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Domain\Process\Models\Process;
use App\Domain\Workflow\Models\WorkflowTransition;
use App\Domain\Process\Services\ProcessService;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ProcessScheduledTransitionsJob implements ShouldQueue
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
        $processes = Process::with(['currentStage.outgoingTransitions', 'workflow', 'histories'])
            ->where('status', 'active')
            ->get();

        $log->info('Processando transições agendadas', ['total_processes' => $processes->count()]);

        foreach ($processes as $process) {
            $scheduledTransitions = $process->currentStage->outgoingTransitions()
                ->where('trigger_type', 'scheduled')
                ->get();

            foreach ($scheduledTransitions as $transition) {
                try {
                    $this->processScheduledTransition($processService, $process, $transition);
                } catch (\Exception $e) {
                    $log->error('Erro ao processar transição agendada', [
                        'process_id' => $process->id,
                        'transition_id' => $transition->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }
    }

    /**
     * Processa uma transição agendada para um processo
     */
    private function processScheduledTransition(ProcessService $processService, Process $process, WorkflowTransition $transition)
    {
        // Verifica se as condições de agendamento são atendidas
        if (!$transition->condition || empty($transition->condition)) {
            return;
        }

        $duration = $transition->condition['duration'] ?? null;
        $unit = $transition->condition['unit'] ?? null;

        if (!$duration || !$unit) {
            return;
        }

        // Obtém a última transição para o estágio atual
        $latestTransition = $process->histories()
            ->where('to_stage_id', $process->current_stage_id)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$latestTransition) {
            return;
        }

        $startDate = Carbon::parse($latestTransition->created_at);
        $minimumDate = $this->calculateMinimumDate(
            $startDate,
            $duration,
            $unit,
            $transition->condition['business_days'] ?? false
        );

        $now = Carbon::now();

        // Verifica se o tempo mínimo foi atingido
        if ($now->gte($minimumDate)) {
            Log::info('Executando transição agendada', [
                'process_id' => $process->id,
                'from_stage' => $process->current_stage_id,
                'to_stage' => $transition->to_stage_id,
                'scheduled_date' => $minimumDate->toDateTimeString()
            ]);

            $processService->moveToNextStage($process, [
                'to_stage_id' => $transition->to_stage_id,
                'comments' => 'Transição agendada executada pelo sistema'
            ]);
        }
    }

    /**
     * Calcula a data mínima para uma transição agendada
     */
    private function calculateMinimumDate(Carbon $startDate, $duration, $unit, $businessDays = false)
    {
        if ($businessDays) {
            return $this->addBusinessDays($startDate, $duration);
        }

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
     * Adiciona apenas dias úteis (segunda a sexta) à data
     */
    private function addBusinessDays(Carbon $date, $days)
    {
        $businessDays = 0;
        while ($businessDays < $days) {
            $date->addDay();
            if (!$date->isWeekend()) {
                $businessDays++;
            }
        }
        return $date;
    }
}
