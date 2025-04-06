<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Domain\Workflow\Models\Workflow;
use App\Domain\Workflow\Models\WorkflowStage;
use App\Domain\Workflow\Models\WorkflowTransition;
use App\Domain\Workflow\Services\WorkflowService;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class WorkflowController extends Controller implements HasMiddleware
{
    protected $workflowService;

    public function __construct(WorkflowService $workflowService)
    {
        $this->workflowService = $workflowService;
    }

    public static function middleware(): array
    {
        /**
         * $this->middleware('can:view workflows')->only(['index', 'show']);
         * $this->middleware('can:manage workflows')->except(['index', 'show']);
         */
        return [
            new Middleware('can:view workflows', only: ['index', 'show']),
            new Middleware('can:manage workflows', except: ['index', 'show']),
        ];
    }
    /**
     * Display a listing of workflows.
     */
    public function index()
    {
        $workflows = Workflow::with(['stages' => function ($query) {
            $query->orderBy('order');
        }])->get();

        return view('workflows.index', compact('workflows'));
    }

    /**
     * Show the form for creating a new workflow.
     */
    public function create()
    {
        return view('workflows.create');
    }

    /**
     * Store a newly created workflow in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'stages' => 'nullable|array',
            'stages.*.name' => 'required|string|max:255',
            'stages.*.description' => 'nullable|string',
            'stages.*.type' => 'string|in:manual,automatic,conditional',
        ]);

        $workflow = $this->workflowService->createWorkflow($validated);

        return redirect()->route('workflows.show', $workflow)
            ->with('success', 'Workflow criado com sucesso!');
    }

    /**
     * Display the specified workflow.
     */
    public function show($id)
    {
        $workflow = Workflow::with([
            'stages' => function ($query) {
                $query->orderBy('order');
            },
            'transitions.fromStage',
            'transitions.toStage',
            'creator'
        ])->findOrFail($id);

        return view('workflows.show', compact('workflow'));
    }

    /**
     * Show the form for editing the specified workflow.
     */
    public function edit($id)
    {
        $workflow = Workflow::with(['stages' => function ($query) {
            $query->orderBy('order');
        }])->findOrFail($id);

        return view('workflows.edit', compact('workflow'));
    }

    /**
     * Update the specified workflow in storage.
     */
    public function update(Request $request, $id)
    {
        $workflow = Workflow::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $workflow = $this->workflowService->updateWorkflow($workflow, $validated);

        return redirect()->route('workflows.show', $workflow)
            ->with('success', 'Workflow atualizado com sucesso!');
    }

    /**
     * Remove the specified workflow from storage.
     */
    public function destroy($id)
    {
        $workflow = Workflow::findOrFail($id);

        // Verificar se não existem processos associados antes de excluir
        if ($workflow->processes()->count() > 0) {
            return redirect()->route('workflows.index')
                ->with('error', 'Não é possível excluir este workflow pois existem processos associados a ele.');
        }

        $workflow->delete();

        return redirect()->route('workflows.index')
            ->with('success', 'Workflow excluído com sucesso!');
    }

    /**
     * Show form to add a stage to the workflow.
     */
    public function createStage($id)
    {
        $workflow = Workflow::findOrFail($id);

        return view('workflows.stages.create', compact('workflow'));
    }

    /**
     * Add a stage to the workflow.
     */
    public function storeStage(Request $request, $id)
    {
        $workflow = Workflow::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'string|in:manual,automatic,conditional',
            'config' => 'nullable|array',
        ]);

        $stage = $this->workflowService->addStage($workflow, $validated);

        return redirect()->route('workflows.show', $workflow)
            ->with('success', 'Estágio adicionado com sucesso!');
    }

    /**
     * Show form to add a transition between stages.
     */
    public function createTransition($id)
    {
        $workflow = Workflow::with(['stages' => function ($query) {
            $query->orderBy('order');
        }])->findOrFail($id);

        if ($workflow->stages->count() < 2) {
            return redirect()->route('workflows.show', $workflow)
                ->with('error', 'É necessário ter pelo menos dois estágios para criar uma transição.');
        }

        return view('workflows.transitions.create', compact('workflow'));
    }

    /**
     * Add a transition between stages.
     */
    public function storeTransition(Request $request, $id)
    {
        $workflow = Workflow::findOrFail($id);

        $validated = $request->validate([
            'from_stage_id' => 'required|exists:workflow_stages,id',
            'to_stage_id' => 'required|exists:workflow_stages,id',
            'condition' => 'nullable|array',
            'trigger_type' => 'string|in:manual,automatic,scheduled',
        ]);

        // Verificar se os estágios pertencem ao workflow
        $fromStage = WorkflowStage::where('id', $validated['from_stage_id'])
            ->where('workflow_id', $workflow->id)
            ->first();

        $toStage = WorkflowStage::where('id', $validated['to_stage_id'])
            ->where('workflow_id', $workflow->id)
            ->first();

        if (!$fromStage || !$toStage) {
            return redirect()->back()
                ->with('error', 'Os estágios selecionados não pertencem a este workflow.')
                ->withInput();
        }

        // Verificar se já existe uma transição igual
        $existingTransition = WorkflowTransition::where('workflow_id', $workflow->id)
            ->where('from_stage_id', $validated['from_stage_id'])
            ->where('to_stage_id', $validated['to_stage_id'])
            ->first();

        if ($existingTransition) {
            return redirect()->back()
                ->with('error', 'Já existe uma transição entre estes estágios.')
                ->withInput();
        }

        $transition = $this->workflowService->addTransition($workflow, $validated);

        return redirect()->route('workflows.show', $workflow)
            ->with('success', 'Transição adicionada com sucesso!');
    }
}
