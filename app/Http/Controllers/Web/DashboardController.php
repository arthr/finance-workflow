<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Domain\Process\Models\Process;
use App\Domain\Workflow\Models\Workflow;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Exibe o dashboard da aplicação com estatísticas e listas de processos
     */
    public function index()
    {
        // Estatísticas globais
        $totalProcesses = Process::count();
        $assignedProcesses = Process::where('assigned_to', Auth::id())->count();
        $workflowsCount = Workflow::where('is_active', true)->count();

        // Total de workflows ativos
        $activeWorkflows = Workflow::where('is_active', true)->count();

        // Processos pendentes/aguardando
        $pendingProcesses = Process::where('status', 'active')->count();

        // Processos por status
        $processByStatus = Process::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        // Processos recentes (10 últimos)
        $recentProcesses = Process::with(['workflow', 'currentStage', 'creator'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Processos atribuídos ao usuário atual
        $myProcesses = Process::with(['workflow', 'currentStage'])
            ->where('assigned_to', Auth::id())
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();

        return view('dashboard', compact(
            'totalProcesses',
            'activeWorkflows',
            'pendingProcesses',
            'assignedProcesses',
            'workflowsCount',
            'processByStatus',
            'recentProcesses',
            'myProcesses'
        ));
    }
}
