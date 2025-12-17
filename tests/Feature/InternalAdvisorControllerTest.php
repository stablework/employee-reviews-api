<?php

namespace Tests\Feature;

use App\Models\InternalAdvisor;
use App\Models\Project;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InternalAdvisorControllerTest extends TestCase
{
    use RefreshDatabase;

    private $executive;

    private $manager1;

    private $manager2;

    private $team1;

    private $team2;

    private $project;

    protected function setUp(): void
    {
        parent::setUp();
        $this->executive = $this->createUserWithRole('Executive');
        $this->manager1 = $this->createUserWithRole('Manager');
        $this->manager2 = $this->createUserWithRole('Manager');

        $this->team1 = Team::create(['name' => 'Team 1', 'manager_id' => $this->manager1->id]);
        $this->team2 = Team::create(['name' => 'Team 2', 'manager_id' => $this->manager2->id]);

        $this->project = Project::create(['name' => 'Project 1']);
        $this->project->teams()->attach($this->team2->id);
    }

    public function test_index_requires_authentication(): void
    {
        $this->getJson('/api/internal-advisors')
            ->assertUnauthorized();
    }

    public function test_index_returns_advisor_assignments(): void
    {
        InternalAdvisor::create([
            'user_id' => $this->manager1->id,
            'project_id' => $this->project->id,
            'team_id' => $this->team2->id,
        ]);

        $this->withApiToken($this->executive)
            ->getJson('/api/internal-advisors')
            ->assertOk()
            ->assertJsonCount(1);
    }

    public function test_manager_can_see_own_advisor_assignments(): void
    {
        InternalAdvisor::create([
            'user_id' => $this->manager1->id,
            'project_id' => $this->project->id,
            'team_id' => $this->team2->id,
        ]);

        $this->withApiToken($this->manager1)
            ->getJson('/api/internal-advisors')
            ->assertOk()
            ->assertJsonCount(1);
    }

    public function test_manager_cannot_see_others_advisor_assignments(): void
    {
        InternalAdvisor::create([
            'user_id' => $this->manager1->id,
            'project_id' => $this->project->id,
            'team_id' => $this->team2->id,
        ]);

        $this->withApiToken($this->manager2)
            ->getJson('/api/internal-advisors')
            ->assertOk()
            ->assertJsonCount(0);
    }

    public function test_manager_can_create_advisor_assignment(): void
    {
        $this->withApiToken($this->manager1)
            ->postJson('/api/internal-advisors', [
                'project_id' => $this->project->id,
                'team_id' => $this->team2->id,
            ])
            ->assertCreated()
            ->assertJsonFragment(['user_id' => $this->manager1->id]);

        $this->assertDatabaseHas('internal_advisors', [
            'user_id' => $this->manager1->id,
            'project_id' => $this->project->id,
        ]);
    }

    public function test_associate_can_create_advisor_assignment(): void
    {
        $associate = $this->createUserWithRole('Associate');

        $this->withApiToken($associate)
            ->postJson('/api/internal-advisors', [
                'project_id' => $this->project->id,
                'team_id' => $this->team2->id,
            ])
            ->assertCreated();
    }

    public function test_executive_cannot_create_advisor_assignment(): void
    {
        $this->withApiToken($this->executive)
            ->postJson('/api/internal-advisors', [
                'project_id' => $this->project->id,
                'team_id' => $this->team2->id,
            ])
            ->assertForbidden();
    }

    public function test_user_can_delete_own_advisor_assignment(): void
    {
        $advisor = InternalAdvisor::create([
            'user_id' => $this->manager1->id,
            'project_id' => $this->project->id,
            'team_id' => $this->team2->id,
        ]);

        $this->withApiToken($this->manager1)
            ->deleteJson("/api/internal-advisors/{$advisor->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('internal_advisors', ['id' => $advisor->id]);
    }

    public function test_executive_can_delete_any_advisor_assignment(): void
    {
        $advisor = InternalAdvisor::create([
            'user_id' => $this->manager1->id,
            'project_id' => $this->project->id,
            'team_id' => $this->team2->id,
        ]);

        $this->withApiToken($this->executive)
            ->deleteJson("/api/internal-advisors/{$advisor->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('internal_advisors', ['id' => $advisor->id]);
    }

    public function test_cannot_delete_others_advisor_assignment(): void
    {
        $advisor = InternalAdvisor::create([
            'user_id' => $this->manager1->id,
            'project_id' => $this->project->id,
            'team_id' => $this->team2->id,
        ]);

        $this->withApiToken($this->manager2)
            ->deleteJson("/api/internal-advisors/{$advisor->id}")
            ->assertForbidden();
    }
}
