<?php

namespace Tests\Unit\Domain\Process\Services;

use Tests\TestCase;
use App\Domain\Process\Models\Process;
use App\Domain\Process\Services\ProcessService;
use App\Domain\Workflow\Models\Workflow;
use App\Domain\Workflow\Models\WorkflowStage;
use App\Domain\Workflow\Models\WorkflowTransition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\Traits\HasPermissionsTrait;

class ProcessServiceTest extends TestCase
{
    use RefreshDatabase;
    use HasPermissionsTrait;

    protected $processService;
    protected $user;
    protected $workflow;
    protected $initialStage;
    protected $secondStage;

    public function setUp(): void
    {
        parent::setUp();

        $this->processService = new ProcessService();

        // Create test user
        $this->user = \App\Models\User::factory()->create();
        Auth::login($this->user);

        // Create workflow with stages
        $this->workflow = Workflow::factory()->create([
            'created_by' => $this->user->id
        ]);

        $this->initialStage = WorkflowStage::factory()->create([
            'workflow_id' => $this->workflow->id,
            'name' => 'Initial Stage',
            'order' => 0
        ]);

        $this->secondStage = WorkflowStage::factory()->create([
            'workflow_id' => $this->workflow->id,
            'name' => 'Second Stage',
            'order' => 1
        ]);

        // Create transition between stages
        WorkflowTransition::factory()->create([
            'workflow_id' => $this->workflow->id,
            'from_stage_id' => $this->initialStage->id,
            'to_stage_id' => $this->secondStage->id,
            'trigger_type' => 'manual'
        ]);
    }

    public function test_can_create_process()
    {
        // Arrange
        $processData = [
            'workflow_id' => $this->workflow->id,
            'title' => 'Test Process',
            'description' => 'Test process description',
            'data' => ['key' => 'value']
        ];

        // Act
        $process = $this->processService->createProcess($processData);

        // Assert
        $this->assertInstanceOf(Process::class, $process);
        $this->assertEquals('Test Process', $process->title);
        $this->assertEquals($this->workflow->id, $process->workflow_id);
        $this->assertEquals($this->initialStage->id, $process->current_stage_id);
        $this->assertEquals('active', $process->status);
        $this->assertEquals(['key' => 'value'], $process->data);

        // Verify history was created
        $this->assertDatabaseHas('process_histories', [
            'process_id' => $process->id,
            'to_stage_id' => $this->initialStage->id,
            'action' => 'process_created',
            'performed_by' => $this->user->id
        ]);
    }

    public function test_can_move_process_to_next_stage()
    {
        // Arrange
        $process = Process::factory()->create([
            'workflow_id' => $this->workflow->id,
            'current_stage_id' => $this->initialStage->id,
            'created_by' => $this->user->id
        ]);

        $moveData = [
            'to_stage_id' => $this->secondStage->id,
            'comments' => 'Moving to next stage'
        ];

        // Act
        $updatedProcess = $this->processService->moveToNextStage($process, $moveData);

        // Assert
        $this->assertEquals($this->secondStage->id, $updatedProcess->current_stage_id);

        // Verify history was created
        $this->assertDatabaseHas('process_histories', [
            'process_id' => $process->id,
            'from_stage_id' => $this->initialStage->id,
            'to_stage_id' => $this->secondStage->id,
            'action' => 'stage_changed',
            'comments' => 'Moving to next stage',
            'performed_by' => $this->user->id
        ]);
    }

    public function test_validates_invalid_transitions()
    {
        // Arrange
        $process = Process::factory()->create([
            'workflow_id' => $this->workflow->id,
            'current_stage_id' => $this->initialStage->id,
            'created_by' => $this->user->id
        ]);

        // Create a third stage with no transition from initial stage
        $thirdStage = WorkflowStage::factory()->create([
            'workflow_id' => $this->workflow->id,
            'name' => 'Third Stage',
            'order' => 2
        ]);

        // Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Transição inválida. O estágio atual não possui uma transição para o estágio solicitado.');

        $this->processService->moveToNextStage($process, [
            'to_stage_id' => $thirdStage->id
        ]);
    }

    public function test_handles_automatic_transitions()
    {
        // Arrange
        // Create a transition with automatic trigger and condition
        $automaticTransition = WorkflowTransition::factory()->create([
            'workflow_id' => $this->workflow->id,
            'from_stage_id' => $this->initialStage->id,
            'to_stage_id' => $this->secondStage->id,
            'trigger_type' => 'automatic',
            'condition' => [
                'field' => 'status',
                'operator' => 'equals',
                'value' => 'approved'
            ]
        ]);

        $process = Process::factory()->create([
            'workflow_id' => $this->workflow->id,
            'current_stage_id' => $this->initialStage->id,
            'created_by' => $this->user->id,
            'data' => ['status' => 'approved']
        ]);

        // Grant the necessary permission to the user
        $this->actingAs($this->user)->withPermission('manage processes');

        // Act
        $updatedProcess = $this->processService->moveToNextStage($process, [
            'to_stage_id' => $this->secondStage->id
        ]);

        // Assert
        $this->assertEquals($this->secondStage->id, $updatedProcess->current_stage_id);
    }

    public function test_validates_scheduled_transition_duration()
    {
        // Arrange
        $scheduledTransition = WorkflowTransition::factory()->create([
            'workflow_id' => $this->workflow->id,
            'from_stage_id' => $this->initialStage->id,
            'to_stage_id' => $this->secondStage->id,
            'trigger_type' => 'scheduled',
            'condition' => [
                'duration' => 24,
                'unit' => 'hours'
            ]
        ]);

        $process = Process::factory()->create([
            'workflow_id' => $this->workflow->id,
            'current_stage_id' => $this->initialStage->id,
            'created_by' => $this->user->id,
            'created_at' => now()->subHour() // Only 1 hour ago
        ]);

        // Create history record for the process
        $process->histories()->create([
            'to_stage_id' => $this->initialStage->id,
            'action' => 'process_created',
            'performed_by' => $this->user->id,
            'created_at' => now()->subHour()
        ]);

        // Grant the necessary permission to the user
        $this->actingAs($this->user)->withPermission('manage processes');

        // Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('O tempo mínimo para transição não foi atingido.');

        $this->processService->moveToNextStage($process, [
            'to_stage_id' => $this->secondStage->id
        ]);
    }
}
