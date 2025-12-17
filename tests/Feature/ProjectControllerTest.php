<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectControllerTest extends TestCase
{
    use RefreshDatabase;

    private $executive;

    private $manager;

    private $team;

    protected function setUp(): void
    {
        parent::setUp();
        $this->executive = $this->createUserWithRole('Executive');
        $this->manager = $this->createUserWithRole('Manager');
        $this->team = Team::create(['name' => 'Dev Team', 'manager_id' => $this->manager->id]);
    }

    public function test_index_requires_authentication(): void
    {
        $this->getJson('/api/projects')
            ->assertUnauthorized();
    }

    public function test_index_returns_projects(): void
    {
        Project::create(['name' => 'Project 1', 'description' => 'Test project']);

        $this->withApiToken($this->executive)
            ->getJson('/api/projects')
            ->assertOk()
            ->assertJsonCount(1);
    }

    public function test_show_project(): void
    {
        $project = Project::create(['name' => 'Project 1', 'description' => 'Test project']);

        $this->withApiToken($this->executive)
            ->getJson("/api/projects/{$project->id}")
            ->assertOk()
            ->assertJsonFragment(['id' => $project->id, 'name' => 'Project 1']);
    }

    public function test_create_project_requires_executive_role(): void
    {
        $this->withApiToken($this->manager)
            ->postJson('/api/projects', [
                'name' => 'New Project',
                'description' => 'Test',
            ])
            ->assertForbidden();
    }

    public function test_executive_can_create_project(): void
    {
        $this->withApiToken($this->executive)
            ->postJson('/api/projects', [
                'name' => 'New Project',
                'description' => 'Test',
            ])
            ->assertCreated()
            ->assertJsonFragment(['name' => 'New Project']);

        $this->assertDatabaseHas('projects', ['name' => 'New Project']);
    }

    public function test_create_project_requires_unique_name(): void
    {
        Project::create(['name' => 'Existing Project']);

        $this->withApiToken($this->executive)
            ->postJson('/api/projects', [
                'name' => 'Existing Project',
                'description' => 'Test',
            ])
            ->assertUnprocessable();
    }

    public function test_update_project_requires_executive_role(): void
    {
        $project = Project::create(['name' => 'Project 1']);

        $this->withApiToken($this->manager)
            ->putJson("/api/projects/{$project->id}", ['name' => 'Updated Project'])
            ->assertForbidden();
    }

    public function test_executive_can_update_project(): void
    {
        $project = Project::create(['name' => 'Project 1']);

        $this->withApiToken($this->executive)
            ->putJson("/api/projects/{$project->id}", ['name' => 'Updated Project'])
            ->assertOk()
            ->assertJsonFragment(['name' => 'Updated Project']);
    }

    public function test_delete_project_requires_executive_role(): void
    {
        $project = Project::create(['name' => 'Project 1']);

        $this->withApiToken($this->manager)
            ->deleteJson("/api/projects/{$project->id}")
            ->assertForbidden();
    }

    public function test_executive_can_delete_project(): void
    {
        $project = Project::create(['name' => 'Project 1']);

        $this->withApiToken($this->executive)
            ->deleteJson("/api/projects/{$project->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }

    public function test_executive_can_assign_team_to_project(): void
    {
        $project = Project::create(['name' => 'Project 1']);

        $this->withApiToken($this->executive)
            ->postJson("/api/projects/{$project->id}/teams", ['team_id' => $this->team->id])
            ->assertOk();

        $this->assertTrue($project->teams()->where('team_id', $this->team->id)->exists());
    }

    public function test_executive_can_remove_team_from_project(): void
    {
        $project = Project::create(['name' => 'Project 1']);
        $project->teams()->attach($this->team->id);

        $this->withApiToken($this->executive)
            ->deleteJson("/api/projects/{$project->id}/teams/{$this->team->id}")
            ->assertNoContent();

        $this->assertFalse($project->teams()->where('team_id', $this->team->id)->exists());
    }
}
