<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;
use App\Models\Project;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $member = User::create([
            'name' => 'Member User',
            'email' => 'member@example.com',
            'password' => Hash::make('password'),
            'role' => 'member',
        ]);

        $project = Project::create([
            'name' => 'Sample Project',
            'description' => 'A sample project for testing',
            'owner_id' => $member->id,
        ]);

        $project->tasks()->create([
            'title' => 'Sample Task',
            'description' => 'A sample task for testing',
            'status' => 'todo',
            'priority' => 'medium',
            'assignee_id' => $member->id,
        ]);
    }
}
