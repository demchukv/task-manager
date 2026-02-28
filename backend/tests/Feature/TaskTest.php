<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_add_task_to_project()
    {
        $user = User::create(['name' => 'Tester', 'email' => 'tester@example.com', 'password' => 'password']);
        $project = Project::create(['name' => 'Test Project', 'owner_id' => $user->id]);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson("/api/projects/{$project->id}/tasks", [
                'title' => 'New Task',
                'description' => 'Task Description',
                'priority' => 'high',
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('tasks', ['title' => 'New Task', 'project_id' => $project->id]);
    }

    public function test_user_can_update_task_status()
    {
        $user = User::create(['name' => 'Tester', 'email' => 'tester@example.com', 'password' => 'password']);
        $project = Project::create(['name' => 'Test Project', 'owner_id' => $user->id]);
        $task = $project->tasks()->create(['title' => 'Old Title', 'status' => 'todo']);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson("/api/tasks/{$task->id}", [
                'status' => 'done',
            ]);

        $response->assertStatus(200);
        $this->assertEquals('done', $task->fresh()->status);
    }
}
