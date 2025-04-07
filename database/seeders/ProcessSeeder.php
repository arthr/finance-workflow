<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Domain\Process\Models\Process;
use App\Domain\Process\Models\ProcessHistory;

class ProcessSeeder extends Seeder
{
    public function run()
    {
        $process = Process::firstOrCreate([
            'workflow_id' => 1,
            'title' => 'FTI LOGISTICA E TRANSPORTES LTDA',
            'description' => 'Cadastro da empresa FTI LOGISTICA E TRANSPORTES LTDA',
            'current_stage_id' => 1,
            'status' => 'active',
            'created_by' => 1,
            'assigned_to' => 1,
        ]);

        ProcessHistory::firstOrCreate([
            'process_id' => $process->id,
            'from_stage_id' => null,
            'to_stage_id' => 1,
            'action' => 'process_created',
            'comments' => 'Priorizar cadastro, precisa operar ainda hoje. Pré-aprovado pela Diretoria. Falar com Altamir.',
            'performed_by' => 1,
        ]);
    }
}
