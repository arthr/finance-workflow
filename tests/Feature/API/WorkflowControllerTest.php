<?php

namespace Tests\Feature\API;

use Tests\TestCase;
use App\Models\User;
use App\Domain\Workflow\Models\Workflow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class WorkflowControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_can_list_workflows()
    {
        // Arrange
        Sanctum::actingAs($this->user);
        Workflow::factory()->count(3)->create(['created_by' => $this->user->id]);

        // Act
        $response = $this->getJson('/api/workflows');

        // Assert
        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    public function test_can_create_workflow()
    {
        // Arrange
        Sanctum::actingAs($this->user);
        $workflowData = [
            'name' => 'API Test Workflow',
            'description' => 'Created via API test',
            'is_active' => true
        ];

        // Act
        $response = $this->postJson('/api/workflows', $workflowData);

        // Assert
        $response->assertStatus(201)
            ->assertJsonFragment([
                'name' => 'API Test Workflow',
                'description' => 'Created via API test'
            ]);
    }

    public function test_can_get_single_workflow()
    {
        // Arrange
        Sanctum::actingAs($this->user);
        $workflow = Workflow::factory()->create([
            'name' => 'Test Workflow',
            'created_by' => $this->user->id
        ]);

        // Act
        $response = $this->getJson("/api/workflows/{$workflow->id}");

        // Assert
        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $workflow->id,
                'name' => 'Test Workflow'
            ]);
    }

    public function test_can_update_workflow()
    {
        // Arrange
        Sanctum::actingAs($this->user);
        $workflow = Workflow::factory()->create([
            'created_by' => $this->user->id
        ]);

        // Act
        $response = $this->putJson("/api/workflows/{$workflow->id}", [
            'name' => 'Updated Workflow',
            'description' => 'Updated description',
            'is_active' => true
        ]);

        // Assert
        $response->assertStatus(200)
            ->assertJsonFragment([
                'name' => 'Updated Workflow',
                'description' => 'Updated description'
            ]);
    }

    public function test_can_delete_workflow()
    {
        // Arrange
        Sanctum::actingAs($this->user);
        $workflow = Workflow::factory()->create([
            'created_by' => $this->user->id
        ]);

        // Act
        $response = $this->deleteJson("/api/workflows/{$workflow->id}");

        // Assert
        $response->assertStatus(204);
        $this->assertDatabaseMissing('workflows', ['id' => $workflow->id]);
    }
}
