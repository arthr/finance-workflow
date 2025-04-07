<?php

namespace Tests\Unit\Domain\Workflow\Services;

use Tests\TestCase;
use App\Domain\Workflow\Models\Workflow;
use App\Domain\Workflow\Models\WorkflowStage;
use App\Domain\Workflow\Models\WorkflowTransition;
use App\Domain\Workflow\Services\WorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

class WorkflowServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $workflowService;

    public function setUp(): void
    {
        parent::setUp();
        $this->workflowService = new WorkflowService();
    }

    public function test_can_create_workflow()
    {
        // Arrange
        $user = \App\Models\User::factory()->create();
        Auth::login($user);

        $data = [
            'name' => 'Test Workflow',
            'description' => 'Test workflow description',
            'is_active' => true
        ];

        // Act
        $workflow = $this->workflowService->createWorkflow($data);

        // Assert
        $this->assertInstanceOf(Workflow::class, $workflow);
        $this->assertEquals('Test Workflow', $workflow->name);
        $this->assertEquals('Test workflow description', $workflow->description);
        $this->assertTrue($workflow->is_active);
    }

    public function test_can_add_stage_to_workflow()
    {
        // Arrange
        $user = \App\Models\User::factory()->create();
        Auth::login($user);

        $workflow = Workflow::factory()->create([
            'created_by' => $user->id
        ]);

        $stageData = [
            'name' => 'First Stage',
            'description' => 'Initial stage',
            'type' => 'manual'
        ];

        // Act
        $stage = $this->workflowService->addStage($workflow, $stageData);

        // Assert
        $this->assertInstanceOf(WorkflowStage::class, $stage);
        $this->assertEquals('First Stage', $stage->name);
        $this->assertEquals('manual', $stage->type);
        $this->assertEquals($workflow->id, $stage->workflow_id);
    }

    public function test_can_add_transition_between_stages()
    {
        // Arrange
        $user = \App\Models\User::factory()->create();
        Auth::login($user);

        $workflow = Workflow::factory()->create([
            'created_by' => $user->id
        ]);

        $fromStage = WorkflowStage::factory()->create([
            'workflow_id' => $workflow->id,
            'name' => 'Stage 1',
            'type' => 'manual'
        ]);

        $toStage = WorkflowStage::factory()->create([
            'workflow_id' => $workflow->id,
            'name' => 'Stage 2',
            'type' => 'manual'
        ]);

        $transitionData = [
            'from_stage_id' => $fromStage->id,
            'to_stage_id' => $toStage->id,
            'trigger_type' => 'manual'
        ];

        // Act
        $transition = $this->workflowService->addTransition($workflow, $transitionData);

        // Assert
        $this->assertInstanceOf(WorkflowTransition::class, $transition);
        $this->assertEquals($fromStage->id, $transition->from_stage_id);
        $this->assertEquals($toStage->id, $transition->to_stage_id);
        $this->assertEquals('manual', $transition->trigger_type);
    }

    public function test_validates_transition_types_correctly()
    {
        // Arrange
        $user = \App\Models\User::factory()->create();
        Auth::login($user);

        $workflow = Workflow::factory()->create([
            'created_by' => $user->id
        ]);

        $fromStage = WorkflowStage::factory()->create([
            'workflow_id' => $workflow->id,
            'name' => 'Stage 1',
            'type' => 'manual'
        ]);

        $toStage = WorkflowStage::factory()->create([
            'workflow_id' => $workflow->id,
            'name' => 'Stage 2',
            'type' => 'manual'
        ]);

        // Act & Assert - Automatic transition requires conditions
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Transições automáticas requerem condições com campo, operador e valor.');

        $this->workflowService->addTransition($workflow, [
            'from_stage_id' => $fromStage->id,
            'to_stage_id' => $toStage->id,
            'trigger_type' => 'automatic'
        ]);
    }

    public function test_validates_automatic_transition_conditions()
    {
        // Arrange
        $user = \App\Models\User::factory()->create();
        Auth::login($user);

        $workflow = Workflow::factory()->create([
            'created_by' => $user->id
        ]);

        $fromStage = WorkflowStage::factory()->create([
            'workflow_id' => $workflow->id,
            'name' => 'Stage 1',
            'type' => 'manual'
        ]);

        $toStage = WorkflowStage::factory()->create([
            'workflow_id' => $workflow->id,
            'name' => 'Stage 2',
            'type' => 'manual'
        ]);

        $transitionData = [
            'from_stage_id' => $fromStage->id,
            'to_stage_id' => $toStage->id,
            'trigger_type' => 'automatic',
            'condition' => [
                'field' => 'status',
                'operator' => 'equals',
                'value' => 'approved'
            ]
        ];

        // Act
        $transition = $this->workflowService->addTransition($workflow, $transitionData);

        // Assert
        $this->assertEquals('automatic', $transition->trigger_type);
        $this->assertEquals('status', $transition->condition['field']);
        $this->assertEquals('equals', $transition->condition['operator']);
        $this->assertEquals('approved', $transition->condition['value']);
    }

    public function test_prevents_duplicate_transitions()
    {
        // Arrange
        $user = \App\Models\User::factory()->create();
        Auth::login($user);

        $workflow = Workflow::factory()->create([
            'created_by' => $user->id
        ]);

        $fromStage = WorkflowStage::factory()->create([
            'workflow_id' => $workflow->id,
            'name' => 'Stage 1'
        ]);

        $toStage = WorkflowStage::factory()->create([
            'workflow_id' => $workflow->id,
            'name' => 'Stage 2'
        ]);

        // Create first transition
        $this->workflowService->addTransition($workflow, [
            'from_stage_id' => $fromStage->id,
            'to_stage_id' => $toStage->id,
            'trigger_type' => 'manual'
        ]);

        // Act & Assert - Adding duplicate should throw exception
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Já existe uma transição entre estes estágios.');

        $this->workflowService->addTransition($workflow, [
            'from_stage_id' => $fromStage->id,
            'to_stage_id' => $toStage->id,
            'trigger_type' => 'manual'
        ]);
    }
}
