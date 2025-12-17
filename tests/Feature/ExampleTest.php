<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_application_returns_a_successful_response(): void
    {
        $executive = $this->createUserWithRole('Executive');

        $response = $this->withApiToken($executive)->getJson('/api/users');

        $response->assertStatus(200);
    }
}
