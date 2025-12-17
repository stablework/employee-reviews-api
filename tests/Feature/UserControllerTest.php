<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    private $executive;

    protected function setUp(): void
    {
        parent::setUp();
        $this->executive = $this->createUserWithRole('Executive');
    }

    public function test_index_requires_authentication(): void
    {
        $this->getJson('/api/users')
            ->assertUnauthorized();
    }

    public function test_index_requires_executive_role(): void
    {
        $manager = $this->createUserWithRole('Manager');

        $this->withApiToken($manager)
            ->getJson('/api/users')
            ->assertForbidden();
    }

    public function test_executive_can_list_users(): void
    {
        $response = $this->withApiToken($this->executive)
            ->getJson('/api/users')
            ->assertOk();

        $this->assertIsArray($response->json());
    }

    public function test_show_user(): void
    {
        $user = $this->createUserWithRole('Manager');

        $this->withApiToken($this->executive)
            ->getJson("/api/users/{$user->id}")
            ->assertOk()
            ->assertJsonFragment(['id' => $user->id, 'email' => $user->email]);
    }

    public function test_create_user_requires_executive_role(): void
    {
        $manager = $this->createUserWithRole('Manager');

        $this->withApiToken($manager)
            ->postJson('/api/users', [
                'name' => 'New User',
                'email' => 'newuser@test.com',
                'password' => 'password123',
            ])
            ->assertForbidden();
    }

    public function test_executive_can_create_user(): void
    {
        $this->withApiToken($this->executive)
            ->postJson('/api/users', [
                'name' => 'New User',
                'email' => 'newuser@test.com',
                'password' => 'password123',
            ])
            ->assertCreated()
            ->assertJsonFragment(['name' => 'New User', 'email' => 'newuser@test.com']);

        $this->assertDatabaseHas('users', ['email' => 'newuser@test.com']);
    }

    public function test_create_user_requires_valid_data(): void
    {
        $this->withApiToken($this->executive)
            ->postJson('/api/users', [
                'name' => 'New User',
            ])
            ->assertUnprocessable();
    }

    public function test_update_user(): void
    {
        $user = $this->createUserWithRole('Manager');

        $this->withApiToken($this->executive)
            ->putJson("/api/users/{$user->id}", [
                'name' => 'Updated Name',
            ])
            ->assertOk()
            ->assertJsonFragment(['name' => 'Updated Name']);
    }

    public function test_delete_user_requires_executive_role(): void
    {
        $manager = $this->createUserWithRole('Manager');
        $targetUser = $this->createUserWithRole('Associate');

        $this->withApiToken($manager)
            ->deleteJson("/api/users/{$targetUser->id}")
            ->assertForbidden();
    }

    public function test_executive_can_delete_user(): void
    {
        $user = $this->createUserWithRole('Associate');

        $this->withApiToken($this->executive)
            ->deleteJson("/api/users/{$user->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}
