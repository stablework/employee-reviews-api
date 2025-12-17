<?php

namespace Tests;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function withApiToken(User $user)
    {
        return $this->withHeader('X-API-Token', $user->api_token);
    }

    protected function createUserWithRole(string $roleName): User
    {
        $user = User::create([
            'name' => "Test {$roleName}",
            'email' => strtolower($roleName).'+'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'api_token' => \Illuminate\Support\Str::random(80),
        ]);

        $role = Role::where('name', $roleName)->firstOrCreate(['name' => $roleName]);
        $user->roles()->attach($role->id);

        return $user;
    }
}
