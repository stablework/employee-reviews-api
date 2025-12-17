<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::create(['name' => 'Executive']);
        Role::create(['name' => 'Manager']);
        Role::create(['name' => 'Associate']);
        Role::create(['name' => 'Internal Advisor']);
    }
}
