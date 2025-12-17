<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Review;
use App\Models\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewControllerTest extends TestCase
{
    use RefreshDatabase;

    private $executive;

    private $manager;

    private $associate1;

    private $associate2;

    private $team;

    private $project;

    protected function setUp(): void
    {
        parent::setUp();
        $this->executive = $this->createUserWithRole('Executive');
        $this->manager = $this->createUserWithRole('Manager');
        $this->associate1 = $this->createUserWithRole('Associate');
        $this->associate2 = $this->createUserWithRole('Associate');

        $this->team = Team::create(['name' => 'Dev Team', 'manager_id' => $this->manager->id]);
        $this->team->members()->attach([$this->associate1->id, $this->associate2->id]);

        $this->project = Project::create(['name' => 'Project 1']);
        $this->project->teams()->attach($this->team->id);
    }

    public function test_index_requires_authentication(): void
    {
        $this->getJson('/api/reviews')
            ->assertUnauthorized();
    }

    public function test_executive_can_see_all_reviews(): void
    {
        Review::create([
            'reviewer_id' => $this->associate1->id,
            'project_id' => $this->project->id,
            'content' => 'Good project',
        ]);

        $this->withApiToken($this->executive)
            ->getJson('/api/reviews')
            ->assertOk()
            ->assertJsonCount(1);
    }

    public function test_manager_can_see_team_project_reviews(): void
    {
        Review::create([
            'reviewer_id' => $this->associate1->id,
            'project_id' => $this->project->id,
            'content' => 'Good project',
        ]);

        $this->withApiToken($this->manager)
            ->getJson('/api/reviews')
            ->assertOk()
            ->assertJsonCount(1);
    }

    public function test_associate_can_see_team_project_reviews(): void
    {
        Review::create([
            'reviewer_id' => $this->associate1->id,
            'project_id' => $this->project->id,
            'content' => 'Good project',
        ]);

        $this->withApiToken($this->associate2)
            ->getJson('/api/reviews')
            ->assertOk()
            ->assertJsonCount(1);
    }

    public function test_show_review(): void
    {
        $review = Review::create([
            'reviewer_id' => $this->associate1->id,
            'project_id' => $this->project->id,
            'content' => 'Good project',
        ]);

        $this->withApiToken($this->executive)
            ->getJson("/api/reviews/{$review->id}")
            ->assertOk()
            ->assertJsonFragment(['id' => $review->id]);
    }

    public function test_create_project_review(): void
    {
        $this->withApiToken($this->associate1)
            ->postJson('/api/reviews', [
                'project_id' => $this->project->id,
                'content' => 'Great project!',
            ])
            ->assertCreated()
            ->assertJsonFragment(['content' => 'Great project!']);

        $this->assertDatabaseHas('reviews', ['content' => 'Great project!']);
    }

    public function test_create_peer_review(): void
    {
        $this->withApiToken($this->associate1)
            ->postJson('/api/reviews', [
                'reviewee_id' => $this->associate2->id,
                'content' => 'Great team member!',
            ])
            ->assertCreated();
    }

    public function test_create_review_requires_project_or_reviewee(): void
    {
        $this->withApiToken($this->associate1)
            ->postJson('/api/reviews', [
                'content' => 'No project or reviewee',
            ])
            ->assertUnprocessable();
    }

    public function test_update_own_review(): void
    {
        $review = Review::create([
            'reviewer_id' => $this->associate1->id,
            'project_id' => $this->project->id,
            'content' => 'Original content',
        ]);

        $this->withApiToken($this->associate1)
            ->putJson("/api/reviews/{$review->id}", [
                'content' => 'Updated content',
            ])
            ->assertOk()
            ->assertJsonFragment(['content' => 'Updated content']);
    }

    public function test_cannot_update_others_review(): void
    {
        $review = Review::create([
            'reviewer_id' => $this->associate1->id,
            'project_id' => $this->project->id,
            'content' => 'Original content',
        ]);

        $this->withApiToken($this->associate2)
            ->putJson("/api/reviews/{$review->id}", [
                'content' => 'Hacked content',
            ])
            ->assertForbidden();
    }

    public function test_delete_own_review(): void
    {
        $review = Review::create([
            'reviewer_id' => $this->associate1->id,
            'project_id' => $this->project->id,
            'content' => 'Original content',
        ]);

        $this->withApiToken($this->associate1)
            ->deleteJson("/api/reviews/{$review->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }

    public function test_executive_can_delete_any_review(): void
    {
        $review = Review::create([
            'reviewer_id' => $this->associate1->id,
            'project_id' => $this->project->id,
            'content' => 'Original content',
        ]);

        $this->withApiToken($this->executive)
            ->deleteJson("/api/reviews/{$review->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }

    public function test_cannot_delete_others_review(): void
    {
        $review = Review::create([
            'reviewer_id' => $this->associate1->id,
            'project_id' => $this->project->id,
            'content' => 'Original content',
        ]);

        $this->withApiToken($this->associate2)
            ->deleteJson("/api/reviews/{$review->id}")
            ->assertForbidden();
    }

    public function test_reviewer_name_hidden_from_non_executives(): void
    {
        $review = Review::create([
            'reviewer_id' => $this->associate1->id,
            'project_id' => $this->project->id,
            'content' => 'Good project',
        ]);

        $response = $this->withApiToken($this->associate2)
            ->getJson("/api/reviews/{$review->id}")
            ->assertOk();

        $this->assertArrayNotHasKey('reviewer', $response->json());
    }

    public function test_reviewer_name_visible_to_reviewer(): void
    {
        $review = Review::create([
            'reviewer_id' => $this->associate1->id,
            'project_id' => $this->project->id,
            'content' => 'Good project',
        ]);

        $response = $this->withApiToken($this->associate1)
            ->getJson("/api/reviews/{$review->id}")
            ->assertOk();

        $this->assertArrayHasKey('reviewer', $response->json());
    }

    public function test_reviewer_name_visible_to_executive(): void
    {
        $review = Review::create([
            'reviewer_id' => $this->associate1->id,
            'project_id' => $this->project->id,
            'content' => 'Good project',
        ]);

        $response = $this->withApiToken($this->executive)
            ->getJson("/api/reviews/{$review->id}")
            ->assertOk();

        $this->assertArrayHasKey('reviewer', $response->json());
    }
}
