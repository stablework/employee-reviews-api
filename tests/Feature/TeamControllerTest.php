<?php

namespace Tests\Feature;

use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamControllerTest extends TestCase
{
    use RefreshDatabase;

    private $executive;

    private $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->executive = $this->createUserWithRole('Executive');
        $this->manager = $this->createUserWithRole('Manager');
    }

    public function test_index_requires_authentication(): void
    {
        $this->getJson('/api/teams')
            ->assertUnauthorized();
    }

    public function test_index_returns_teams(): void
    {
        Team::create(['name' => 'Dev Team', 'manager_id' => $this->manager->id]);

        $this->withApiToken($this->executive)
            ->getJson('/api/teams')
            ->assertOk()
            ->assertJsonCount(1);
    }

    public function test_show_team(): void
    {
        $team = Team::create(['name' => 'Dev Team', 'manager_id' => $this->manager->id]);

        $this->withApiToken($this->executive)
            ->getJson("/api/teams/{$team->id}")
            ->assertOk()
            ->assertJsonFragment(['id' => $team->id, 'name' => 'Dev Team']);
    }

    public function test_create_team_requires_executive_role(): void
    {
        $this->withApiToken($this->manager)
            ->postJson('/api/teams', [
                'name' => 'New Team',
                'manager_id' => $this->manager->id,
            ])
            ->assertForbidden();
    }

    public function test_executive_can_create_team(): void
    {
        $this->withApiToken($this->executive)
            ->postJson('/api/teams', [
                'name' => 'New Team',
                'manager_id' => $this->manager->id,
            ])
            ->assertCreated()
            ->assertJsonFragment(['name' => 'New Team']);

        $this->assertDatabaseHas('teams', ['name' => 'New Team']);
    }

    public function test_update_team_requires_executive_role(): void
    {
        $team = Team::create(['name' => 'Dev Team', 'manager_id' => $this->manager->id]);

        $this->withApiToken($this->manager)
            ->putJson("/api/teams/{$team->id}", ['name' => 'Updated Team'])
            ->assertForbidden();
    }

    public function test_executive_can_update_team(): void
    {
        $team = Team::create(['name' => 'Dev Team', 'manager_id' => $this->manager->id]);

        $this->withApiToken($this->executive)
            ->putJson("/api/teams/{$team->id}", ['name' => 'Updated Team'])
            ->assertOk()
            ->assertJsonFragment(['name' => 'Updated Team']);
    }

    public function test_delete_team_requires_executive_role(): void
    {
        $team = Team::create(['name' => 'Dev Team', 'manager_id' => $this->manager->id]);

        $this->withApiToken($this->manager)
            ->deleteJson("/api/teams/{$team->id}")
            ->assertForbidden();
    }

    public function test_executive_can_delete_team(): void
    {
        $team = Team::create(['name' => 'Dev Team', 'manager_id' => $this->manager->id]);

        $this->withApiToken($this->executive)
            ->deleteJson("/api/teams/{$team->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('teams', ['id' => $team->id]);
    }

    public function test_executive_can_add_member_to_team(): void
    {
        $team = Team::create(['name' => 'Dev Team', 'manager_id' => $this->manager->id]);
        $associate = $this->createUserWithRole('Associate');

        $this->withApiToken($this->executive)
            ->postJson("/api/teams/{$team->id}/members", ['user_id' => $associate->id])
            ->assertOk();

        $this->assertTrue($team->members()->where('user_id', $associate->id)->exists());
    }

    public function test_executive_can_remove_member_from_team(): void
    {
        $team = Team::create(['name' => 'Dev Team', 'manager_id' => $this->manager->id]);
        $associate = $this->createUserWithRole('Associate');
        $team->members()->attach($associate->id);

        $this->withApiToken($this->executive)
            ->deleteJson("/api/teams/{$team->id}/members/{$associate->id}")
            ->assertNoContent();

        $this->assertFalse($team->members()->where('user_id', $associate->id)->exists());
    }
}
