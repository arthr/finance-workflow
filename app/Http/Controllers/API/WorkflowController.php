<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Domain\Workflow\Models\Workflow;
use App\Domain\Workflow\Services\WorkflowService;

class WorkflowController extends Controller
{
    protected $workflowService;

    public function __construct(WorkflowService $workflowService)
    {
        $this->workflowService = $workflowService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $workflows = Workflow::with(['stages' => function ($query) {
            $query->orderBy('order');
        }])->get();

        return response()->json($workflows);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'stages' => 'array',
            'stages.*.name' => 'required|string|max:255',
            'stages.*.description' => 'nullable|string',
            'stages.*.type' => 'string|in:manual,automatic,conditional',
            'stages.*.config' => 'nullable|array'
        ]);

        $workflow = $this->workflowService->createWorkflow($validated);

        return response()->json($workflow->load('stages'), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $workflow = Workflow::with(['stages' => function ($query) {
            $query->orderBy('order');
        }, 'transitions.fromStage', 'transitions.toStage'])->findOrFail($id);

        return response()->json($workflow);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $workflow = Workflow::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $workflow = $this->workflowService->updateWorkflow($workflow, $validated);

        return response()->json($workflow);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $workflow = Workflow::findOrFail($id);
        $workflow->delete();

        return response()->json(null, 204);
    }

    /**
     * Add a stage to the workflow.
     */
    public function addStage(Request $request, string $id)
    {
        $workflow = Workflow::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'string|in:manual,automatic,conditional',
            'config' => 'nullable|array'
        ]);

        $stage = $this->workflowService->addStage($workflow, $validated);

        return response()->json($stage, 201);
    }

    /**
     * Add a transition between stages.
     */
    public function addTransition(Request $request, string $id)
    {
        $workflow = Workflow::findOrFail($id);

        $validated = $request->validate([
            'from_stage_id' => 'required|exists:workflow_stages,id',
            'to_stage_id' => 'required|exists:workflow_stages,id',
            'condition' => 'nullable|array',
            'trigger_type' => 'string|in:manual,automatic,scheduled',
        ]);

        $transition = $this->workflowService->addTransition($workflow, $validated);

        return response()->json($transition, 201);
    }
}
