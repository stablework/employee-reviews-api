<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\Team;
use App\Models\Project;
use App\Models\Review;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call(RoleSeeder::class);

        $executive = User::create([
            'name' => 'John Executive',
            'email' => 'executive@example.com',
            'password' => bcrypt('password'),
            'api_token' => Str::random(80),
        ]);
        $executive->roles()->attach(Role::where('name', 'Executive')->first());

        $manager1 = User::create([
            'name' => 'Alice Manager',
            'email' => 'alice@example.com',
            'password' => bcrypt('password'),
            'api_token' => Str::random(80),
        ]);
        $manager1->roles()->attach(Role::where('name', 'Manager')->first());

        $manager2 = User::create([
            'name' => 'Bob Manager',
            'email' => 'bob@example.com',
            'password' => bcrypt('password'),
            'api_token' => Str::random(80),
        ]);
        $manager2->roles()->attach(Role::where('name', 'Manager')->first());

        $associate1 = User::create([
            'name' => 'Charlie Associate',
            'email' => 'charlie@example.com',
            'password' => bcrypt('password'),
            'api_token' => Str::random(80),
        ]);
        $associate1->roles()->attach(Role::where('name', 'Associate')->first());

        $associate2 = User::create([
            'name' => 'Diana Associate',
            'email' => 'diana@example.com',
            'password' => bcrypt('password'),
            'api_token' => Str::random(80),
        ]);
        $associate2->roles()->attach(Role::where('name', 'Associate')->first());

        $associate3 = User::create([
            'name' => 'Eve Associate',
            'email' => 'eve@example.com',
            'password' => bcrypt('password'),
            'api_token' => Str::random(80),
        ]);
        $associate3->roles()->attach(Role::where('name', 'Associate')->first());

        $team1 = Team::create([
            'name' => 'Development Team',
            'manager_id' => $manager1->id,
        ]);

        $team1->members()->attach([$associate1->id, $associate2->id]);

        $team2 = Team::create([
            'name' => 'Design Team',
            'manager_id' => $manager2->id,
        ]);

        $team2->members()->attach([$associate3->id]);

        $project1 = Project::create([
            'name' => 'Mobile App',
            'description' => 'Build a mobile application',
        ]);

        $project2 = Project::create([
            'name' => 'Website Redesign',
            'description' => 'Redesign company website',
        ]);

        $project3 = Project::create([
            'name' => 'API Integration',
            'description' => 'Integrate third-party APIs',
        ]);

        $project1->teams()->attach([$team1->id, $team2->id]);
        $project2->teams()->attach([$team2->id]);
        $project3->teams()->attach([$team1->id]);

        Review::create([
            'reviewer_id' => $associate1->id,
            'project_id' => $project1->id,
            'content' => 'Great project, well organized',
        ]);

        Review::create([
            'reviewer_id' => $associate2->id,
            'reviewee_id' => $associate1->id,
            'content' => 'Charlie is a great team player',
        ]);

        Review::create([
            'reviewer_id' => $manager1->id,
            'project_id' => $project1->id,
            'content' => 'Project is on track',
        ]);
    }
}
