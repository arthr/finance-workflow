<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Domain\Process\Models\Process;
use App\Domain\Workflow\Models\Workflow;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Exibe o dashboard da aplicação com estatísticas e listas de processos
     */
    public function index()
    {
        // Total de processos
        $totalProcesses = Process::count();

        // Processos atribuídos ao usuário atual
        $assignedProcesses = Process::where('assigned_to', Auth::id())->count();

        // Total de workflows ativos
        $activeWorkflows = Workflow::where('is_active', true)->count();

        // Processos pendentes/aguardando
        $pendingProcesses = Process::where('status', 'active')->count();

        // Processos recentes (10 últimos)
        $recentProcesses = Process::with(['workflow', 'current_stage', 'creator'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Processos atribuídos ao usuário atual
        $myProcesses = Process::with(['workflow', 'current_stage'])
            ->where('assigned_to', Auth::id())
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();

        return view('dashboard', compact(
            'totalProcesses',
            'assignedProcesses',
            'activeWorkflows',
            'pendingProcesses',
            'recentProcesses',
            'myProcesses'
        ));
    }
}
