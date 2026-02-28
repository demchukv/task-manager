<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_project()
    {
        $user = User::create([
            'name' => 'Tester',
            'email' => 'tester@example.com',
            'password' => 'password',
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/projects', [
                'name' => 'New Project',
                'description' => 'Test Description',
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('projects', ['name' => 'New Project', 'owner_id' => $user->id]);
    }

    public function test_user_can_list_own_projects()
    {
        $user = User::create(['name' => 'Tester', 'email' => 'tester@example.com', 'password' => 'password']);
        $otherUser = User::create(['name' => 'Other', 'email' => 'other@example.com', 'password' => 'password']);

        Project::create(['name' => 'My Project', 'owner_id' => $user->id]);
        Project::create(['name' => 'Other Project', 'owner_id' => $otherUser->id]);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/projects');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['name' => 'My Project']);
    }

    public function test_admin_can_see_all_projects()
    {
        $admin = User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => 'password', 'role' => 'admin']);
        $user = User::create(['name' => 'Tester', 'email' => 'tester@example.com', 'password' => 'password']);

        Project::create(['name' => 'User Project', 'owner_id' => $user->id]);

        $token = $admin->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/projects');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }
}
