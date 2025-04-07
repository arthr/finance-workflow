<?php

namespace Tests\Feature\API;

use Tests\TestCase;
use App\Models\User;
use App\Domain\Workflow\Models\Workflow;
use App\Domain\Workflow\Models\WorkflowStage;
use App\Domain\Workflow\Models\WorkflowTransition;
use App\Domain\Process\Models\Process;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class ProcessControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $workflow;
    protected $initialStage;
    protected $secondStage;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

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

    public function test_can_list_processes()
    {
        // Arrange
        Sanctum::actingAs($this->user);
        Process::factory()->count(3)->create([
            'workflow_id' => $this->workflow->id,
            'current_stage_id' => $this->initialStage->id,
            'created_by' => $this->user->id
        ]);

        // Act
        $response = $this->getJson('/api/processes');

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'per_page',
                'total'
            ])
            ->assertJsonCount(3, 'data');
    }

    public function test_can_create_process()
    {
        // Arrange
        Sanctum::actingAs($this->user);
        $processData = [
            'workflow_id' => $this->workflow->id,
            'title' => 'API Test Process',
            'description' => 'Created via API test',
            'data' => [
                'key1' => 'value1',
                'key2' => 'value2'
            ]
        ];

        // Act
        $response = $this->postJson('/api/processes', $processData);

        // Assert
        $response->assertStatus(201)
            ->assertJsonFragment([
                'title' => 'API Test Process',
                'description' => 'Created via API test'
            ]);
    }

    public function test_can_get_single_process()
    {
        // Arrange
        Sanctum::actingAs($this->user);
        $process = Process::factory()->create([
            'workflow_id' => $this->workflow->id,
            'current_stage_id' => $this->initialStage->id,
            'title' => 'Test Process',
            'created_by' => $this->user->id
        ]);

        // Act
        $response = $this->getJson("/api/processes/{$process->id}");

        // Assert
        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $process->id,
                'title' => 'Test Process'
            ]);
    }

    public function test_can_move_process_to_next_stage()
    {
        // Arrange
        Sanctum::actingAs($this->user);
        $process = Process::factory()->create([
            'workflow_id' => $this->workflow->id,
            'current_stage_id' => $this->initialStage->id,
            'created_by' => $this->user->id
        ]);

        // Act
        $response = $this->postJson("/api/processes/{$process->id}/move", [
            'to_stage_id' => $this->secondStage->id,
            'comments' => 'Moving via API test'
        ]);

        // Assert
        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $process->id,
                'current_stage_id' => $this->secondStage->id
            ]);

        $this->assertDatabaseHas('process_histories', [
            'process_id' => $process->id,
            'from_stage_id' => $this->initialStage->id,
            'to_stage_id' => $this->secondStage->id,
            'comments' => 'Moving via API test'
        ]);
    }

    public function test_can_get_process_history()
    {
        // Arrange
        Sanctum::actingAs($this->user);
        $process = Process::factory()->create([
            'workflow_id' => $this->workflow->id,
            'current_stage_id' => $this->initialStage->id,
            'created_by' => $this->user->id
        ]);

        // Create some history records
        $process->histories()->create([
            'to_stage_id' => $this->initialStage->id,
            'action' => 'process_created',
            'performed_by' => $this->user->id
        ]);

        // Act
        $response = $this->getJson("/api/processes/{$process->id}/history");

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'per_page',
                'total'
            ])
            ->assertJsonCount(1, 'data');
    }
}
