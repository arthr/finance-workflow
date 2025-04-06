<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Domain\Process\Models\Process;
use App\Domain\Process\Services\ProcessService;

class ProcessController extends Controller
{
    protected $processService;

    public function __construct(ProcessService $processService)
    {
        $this->processService = $processService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $processes = Process::with(['workflow', 'currentStage', 'creator', 'assignee'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($processes);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'workflow_id' => 'required|exists:workflows,id',
            'reference_type' => 'nullable|string',
            'reference_id' => 'nullable|string',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'data' => 'nullable|array',
            'assigned_to' => 'nullable|exists:users,id',
            'comments' => 'nullable|string'
        ]);

        $process = $this->processService->createProcess($validated);

        return response()->json($process->load(['workflow', 'currentStage', 'creator', 'assignee']), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
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

        return response()->json($process);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $process = Process::findOrFail($id);

        $validated = $request->validate([
            'title' => 'string|max:255',
            'description' => 'nullable|string',
            'data' => 'nullable|array',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $process->update($validated);

        return response()->json($process);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Note: In many cases, you'd want to soft delete or mark as inactive instead
        $process = Process::findOrFail($id);
        $process->delete();

        return response()->json(null, 204);
    }

    /**
     * Move the process to the next stage.
     */
    public function moveToNextStage(Request $request, string $id)
    {
        $process = Process::findOrFail($id);

        $validated = $request->validate([
            'to_stage_id' => 'required|exists:workflow_stages,id',
            'assigned_to' => 'nullable|exists:users,id',
            'comments' => 'nullable|string',
        ]);

        $process = $this->processService->moveToNextStage($process, $validated);

        return response()->json($process->load(['currentStage', 'assignee']));
    }

    /**
     * Get process history.
     */
    public function history(string $id)
    {
        $history = Process::findOrFail($id)->histories()
            ->with(['fromStage', 'toStage', 'performer'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($history);
    }
}
