<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DebugTest extends TestCase
{
    use RefreshDatabase;

    public function test_debug(): void
    {
        $executive = $this->createUserWithRole('Executive');

        // Reload the user to ensure relationships are loaded
        $executive = $executive->fresh();

        echo "\nExecutive ID: ".$executive->id;
        echo "\nExecutive Email: ".$executive->email;
        echo "\nExecutive Token: ".$executive->api_token;
        echo "\nExecutive Roles: ".$executive->roles()->pluck('name')->implode(', ');
        echo "\nIs Executive: ".($executive->isExecutive() ? 'YES' : 'NO');
        echo "\n";

        $response = $this->withApiToken($executive)->getJson('/api/users');

        echo "\nResponse Status: ".$response->status();
        echo "\nResponse Body: ".$response->getContent();
        echo "\n";

        $this->assertTrue(true);
    }
}
