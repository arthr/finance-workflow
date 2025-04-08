<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Domain\Process\Jobs\ProcessAutomaticTransitionsJob;
use App\Domain\Process\Jobs\ProcessScheduledTransitionsJob;

class ProcessAutomaticTransitions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:process-automatic-transitions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Processa transições automáticas e agendadas para todos os processos ativos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando processamento de transições automáticas e agendadas...');

        // Disparar jobs para processar transições automáticas
        ProcessAutomaticTransitionsJob::dispatch()->onQueue('workflows');

        // Disparar jobs para processar transições agendadas
        ProcessScheduledTransitionsJob::dispatch()->onQueue('workflows');

        $this->info('Jobs de processamento enviados para a fila com sucesso!');
    }
}
