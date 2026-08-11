<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->user = User::first();
        $this->actingAs($this->user);
    }

    public function test_can_list_all_seeded_projects(): void
    {
        $response = $this->getJson('/api/projects');

        $response->assertStatus(200)
            ->assertJsonCount(10, 'data') // default per_page is 10
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'client_name',
                        'project_name',
                        'description',
                        'status',
                        'priority',
                        'start_date',
                        'due_date',
                        'created_at',
                        'updated_at',
                    ],
                ],
                'links' => [
                    'first',
                    'last',
                    'prev',
                    'next',
                ],
                'meta' => [
                    'current_page',
                    'from',
                    'last_page',
                    'links',
                    'path',
                    'per_page',
                    'to',
                    'total',
                    'statuses',
                    'priorities',
                    'stats' => [
                        'total',
                        'planning',
                        'inProgress',
                        'onHold',
                        'completed',
                    ],
                ],
            ]);

        // Assert that passing custom per_page returns the requested count
        $responseCustom = $this->getJson('/api/projects?per_page=12');
        $responseCustom->assertStatus(200)
            ->assertJsonCount(12, 'data');
    }

    public function test_can_filter_projects_by_status(): void
    {
        $response = $this->getJson('/api/projects?status=In Progress');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertNotEmpty($data);
        foreach ($data as $project) {
            $this->assertEquals('In Progress', $project['status']);
        }
    }

    public function test_can_filter_projects_by_priority(): void
    {
        $response = $this->getJson('/api/projects?priority=High');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertNotEmpty($data);
        foreach ($data as $project) {
            $this->assertEquals('High', $project['priority']);
        }
    }

    public function test_can_search_projects(): void
    {
        $response = $this->getJson('/api/projects?search=Acme');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('Acme Corporation', $data[0]['client_name']);
    }

    public function test_can_get_single_project(): void
    {
        $response = $this->getJson('/api/projects/1');

        $response->assertStatus(200)
            ->assertJsonPath('data.id', 1)
            ->assertJsonPath('data.client_name', 'Acme Corporation');
    }

    public function test_returns_404_for_non_existent_project(): void
    {
        $response = $this->getJson('/api/projects/999');

        $response->assertStatus(404)
            ->assertJson(['message' => 'Project not found']);
    }

    public function test_user_cannot_access_another_users_projects(): void
    {
        $otherUser = User::factory()->create();
        $otherProject = Project::create([
            'user_id' => $otherUser->id,
            'client_name' => 'Secret Client',
            'project_name' => 'Confidential Project',
            'status' => 'Planning',
            'priority' => 'High',
            'start_date' => '2026-09-01',
            'due_date' => '2026-10-01',
        ]);

        $response = $this->getJson("/api/projects/{$otherProject->id}");

        $response->assertStatus(404)
            ->assertJson(['message' => 'Project not found']);
    }

    public function test_can_create_project_with_valid_data(): void
    {
        $payload = [
            'client_name' => 'Stark Industries',
            'project_name' => 'Arc Reactor Interface',
            'description' => 'Build clean monitoring UI.',
            'status' => 'Planning',
            'priority' => 'High',
            'start_date' => '2026-09-01',
            'due_date' => '2026-10-01',
        ];

        $response = $this->postJson('/api/projects', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.client_name', 'Stark Industries')
            ->assertJsonPath('data.project_name', 'Arc Reactor Interface');

        $this->assertDatabaseHas('projects', [
            'user_id' => $this->user->id,
            'client_name' => 'Stark Industries',
            'project_name' => 'Arc Reactor Interface',
        ]);
    }

    public function test_validates_required_fields_when_creating(): void
    {
        $response = $this->postJson('/api/projects', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['client_name', 'project_name', 'status', 'priority', 'start_date', 'due_date']);
    }

    public function test_validates_due_date_must_be_after_or_equal_to_start_date(): void
    {
        $payload = [
            'client_name' => 'Wayne Enterprises',
            'project_name' => 'Batcave OS',
            'status' => 'In Progress',
            'priority' => 'High',
            'start_date' => '2026-09-10',
            'due_date' => '2026-09-05',
        ];

        $response = $this->postJson('/api/projects', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['due_date']);
    }

    public function test_validates_enum_lists_for_status_and_priority(): void
    {
        $payload = [
            'client_name' => 'Cyberdyne Systems',
            'project_name' => 'Skynet Control',
            'status' => 'Super Active',
            'priority' => 'Critical',
            'start_date' => '2026-09-01',
            'due_date' => '2026-09-10',
        ];

        $response = $this->postJson('/api/projects', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status', 'priority']);
    }

    public function test_can_update_project(): void
    {
        $payload = [
            'client_name' => 'Acme Corporation Updated',
            'project_name' => 'Corporate Website Redesign v2',
            'description' => 'Updated description.',
            'status' => 'Completed',
            'priority' => 'Medium',
            'start_date' => '2026-06-01',
            'due_date' => '2026-07-20',
        ];

        $response = $this->putJson('/api/projects/1', $payload);

        $response->assertStatus(200)
            ->assertJsonPath('data.client_name', 'Acme Corporation Updated')
            ->assertJsonPath('data.status', 'Completed');

        $this->assertDatabaseHas('projects', [
            'id' => 1,
            'user_id' => $this->user->id,
            'client_name' => 'Acme Corporation Updated',
            'status' => 'Completed',
        ]);
    }

    public function test_can_delete_project(): void
    {
        $response = $this->deleteJson('/api/projects/1');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Project deleted successfully']);

        $this->assertDatabaseMissing('projects', ['id' => 1]);
    }

    public function test_returns_404_when_deleting_non_existent_project(): void
    {
        $response = $this->deleteJson('/api/projects/999');

        $response->assertStatus(404);
    }

    public function test_can_perform_crud_operations_via_direct_projects_web_api_routes(): void
    {
        // 1. GET /projects (Get All Projects)
        $listResponse = $this->getJson('/projects');
        $listResponse->assertStatus(200)->assertJsonStructure(['data']);

        // 2. POST /projects (Create Project)
        $createResponse = $this->postJson('/projects', [
            'client_name' => 'Direct API Client',
            'project_name' => 'Direct API Project',
            'description' => 'Test description',
            'status' => 'Planning',
            'priority' => 'High',
            'start_date' => '2026-09-01',
            'due_date' => '2026-10-01',
        ]);
        $createResponse->assertStatus(201);
        $newId = $createResponse->json('data.id');

        // 3. GET /projects/:id (Get Single Project)
        $showResponse = $this->getJson("/projects/{$newId}");
        $showResponse->assertStatus(200)->assertJsonPath('data.client_name', 'Direct API Client');

        // 4. PUT /projects/:id (Update Project)
        $updateResponse = $this->putJson("/projects/{$newId}", [
            'client_name' => 'Direct API Client Updated',
            'project_name' => 'Direct API Project',
            'status' => 'In Progress',
            'priority' => 'High',
            'start_date' => '2026-09-01',
            'due_date' => '2026-10-01',
        ]);
        $updateResponse->assertStatus(200)->assertJsonPath('data.client_name', 'Direct API Client Updated');

        // 5. DELETE /projects/:id (Delete Project)
        $deleteResponse = $this->deleteJson("/projects/{$newId}");
        $deleteResponse->assertStatus(200);
    }
}
