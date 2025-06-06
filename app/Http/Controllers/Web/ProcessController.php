<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Domain\Process\Models\Process;
use App\Domain\Process\Services\ProcessService;
use App\Domain\Workflow\Models\Workflow;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Log;

class ProcessController extends Controller implements HasMiddleware
{
    protected $processService;
    protected $logger;

    public function __construct(ProcessService $processService)
    {
        $this->processService = $processService;
        $this->logger = Log::channel('process');
    }

    public static function middleware(): array
    {
        return [
            new Middleware('can:view processes', only: ['index', 'show']),
            new Middleware('can:manage processes', except: ['index', 'show']),
        ];
    }
    /**
     * Display a listing of processes.
     */
    public function index(Request $request)
    {
        $query = Process::with(['workflow', 'currentStage', 'creator', 'assignee']);

        // Filtros
        if ($request->has('workflow_id') && $request->workflow_id) {
            $query->where('workflow_id', $request->workflow_id);
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('assigned_to') && $request->assigned_to) {
            $query->where('assigned_to', $request->assigned_to);
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $processes = $query->orderBy('created_at', 'desc')->paginate(15);

        // Para os filtros no formulário
        $workflows = Workflow::where('is_active', true)->get();
        $users = User::orderBy('name')->get();

        return view('processes.index', compact('processes', 'workflows', 'users'));
    }

    /**
     * Show the form for creating a new process.
     */
    public function create()
    {
        $workflows = Workflow::where('is_active', true)
            ->whereHas('stages')
            ->with(['stages' => function ($query) {
                $query->orderBy('order');
            }])
            ->get();

        if ($workflows->isEmpty()) {
            return redirect()->route('processes.index')
                ->with('error', 'É necessário ter pelo menos um workflow ativo com estágios para criar um processo.');
        }

        $users = User::orderBy('name')->get();

        return view('processes.create', compact('workflows', 'users'));
    }

    /**
     * Store a newly created process in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'workflow_id' => 'required|exists:workflows,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'comments' => 'nullable|string',
            'data' => 'nullable|array',
        ]);

        $process = $this->processService->createProcess($validated);

        return redirect()->route('processes.show', $process)
            ->with('success', 'Processo criado com sucesso!');
    }

    /**
     * Display the specified process.
     */
    public function show($id)
    {
        $process = Process::with([
            'workflow',
            'currentStage',
            'creator',
            'assignee',
            'histories' => function ($query) {
                $query->with(['fromStage', 'toStage', 'performer'])
                    ->orderBy('created_at', 'desc');
            }
        ])->findOrFail($id);

        // Obter as transições disponíveis para o estágio atual
        $availableTransitions = $process->currentStage->outgoingTransitions;

        // Usuários para atribuição
        $users = User::orderBy('name')->get();

        return view('processes.show', compact('process', 'availableTransitions', 'users'));
    }

    /**
     * Show the form for editing the specified process.
     */
    public function edit($id)
    {
        $process = Process::with(['workflow', 'currentStage'])->findOrFail($id);

        // Verificar se o usuário pode editar o processo
        if (!Auth::user()->can('manage processes') && $process->created_by != Auth::id() && $process->assigned_to != Auth::id()) {
            return redirect()->route('processes.show', $process)
                ->with('error', 'Você não tem permissão para editar este processo.');
        }

        $users = User::orderBy('name')->get();

        return view('processes.edit', compact('process', 'users'));
    }

    /**
     * Update the specified process in storage.
     */
    public function update(Request $request, $id)
    {
        $process = Process::findOrFail($id);

        // Verificar se o usuário pode editar o processo
        if (!Auth::user()->can('manage processes') && $process->created_by != Auth::id() && $process->assigned_to != Auth::id()) {
            return redirect()->route('processes.show', $process)
                ->with('error', 'Você não tem permissão para editar este processo.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'data' => 'nullable|array',
        ]);

        $process->update($validated);

        return redirect()->route('processes.show', $process)
            ->with('success', 'Processo atualizado com sucesso!');
    }

    /**
     * Move the process to the next stage.
     */
    public function move(Request $request, $id)
    {
        $process = Process::findOrFail($id);

        // Verificar se o usuário pode mover o processo
        if (!Auth::user()->can('manage processes') && $process->assigned_to != Auth::id()) {
            return redirect()->route('processes.show', $process)
                ->with('error', 'Você não tem permissão para mover este processo.');
        }

        $validated = $request->validate([
            'to_stage_id' => 'required|exists:workflow_stages,id',
            'assigned_to' => 'nullable|exists:users,id',
            'comments' => 'nullable|string',
        ]);

        try {
            $process = $this->processService->moveToNextStage($process, $validated);

            return redirect()->route('processes.show', $process)
                ->with('success', 'Processo movido para o próximo estágio com sucesso!');
        } catch (\InvalidArgumentException $e) {
            // Captura exceções específicas de validação e fornece feedback detalhado
            return redirect()->route('processes.show', $process)
                ->with('error', $e->getMessage())
                ->with('errorType', 'validation');
        } catch (\Exception $e) {
            // Captura qualquer outra exceção genérica
            $this->logger->error('Erro ao mover processo: ' . $e->getMessage(), [
                'process_id' => $process->id,
                'to_stage_id' => $validated['to_stage_id'],
                'user_id' => Auth::id(),
                'exception' => $e
            ]);

            return redirect()->route('processes.show', $process)
                ->with('error', 'Erro ao mover o processo: ' . $e->getMessage())
                ->with('errorType', 'system');
        }
    }

    /**
     * Remove the specified process from storage.
     */
    public function destroy($id)
    {
        $process = Process::findOrFail($id);

        // Verificar se o usuário tem permissão para excluir o processo
        if (!Auth::user()->can('manage processes')) {
            return redirect()->route('processes.index')
                ->with('error', 'Você não tem permissão para excluir processos.');
        }

        // Verificar se o processo pode ser excluído (não estiver concluído ou cancelado)
        if ($process->status === 'completed' || $process->status === 'cancelled') {
            return redirect()->route('processes.index')
                ->with('error', 'Processos concluídos ou cancelados não podem ser excluídos.');
        }

        try {
            $process->delete();
            return redirect()->route('processes.index')
                ->with('success', 'Processo excluído com sucesso!');
        } catch (\Exception $e) {
            $this->logger->error('Erro ao excluir processo: ' . $e->getMessage(), [
                'process_id' => $process->id,
                'user_id' => Auth::id(),
                'exception' => $e
            ]);

            return redirect()->route('processes.index')
                ->with('error', 'Erro ao excluir o processo: ' . $e->getMessage());
        }
    }

    /**
     * Display process history.
     */
    public function history($id)
    {
        $process = Process::with(['workflow', 'currentStage'])->findOrFail($id);

        $history = $process->histories()
            ->with(['fromStage', 'toStage', 'performer'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('processes.history', compact('process', 'history'));
    }
}
