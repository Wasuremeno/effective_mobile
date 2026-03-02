<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class TaskApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a user and generate token
        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test-token')->plainTextToken;
    }

    /** @test */
    public function can_get_all_tasks()
    {
        // Create some tasks
        Task::factory()->count(3)->create(['user_id' => $this->user->id]);
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->getJson('/api/tasks');
        
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data' => [
                         '*' => ['id', 'title', 'description', 'status', 'created_at', 'updated_at']
                     ]
                 ]);
    }

    /** @test */
    public function can_create_task()
    {
        $taskData = [
            'title' => 'Complete project documentation',
            'description' => 'Write comprehensive API documentation for the task management system',
            'status' => 'pending'
        ];
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->postJson('/api/tasks', $taskData);
        
        $response->assertStatus(201)
                 ->assertJson([
                     'data' => [
                         'title' => $taskData['title'],
                         'description' => $taskData['description'],
                         'status' => $taskData['status']
                     ]
                 ]);
        
        $this->assertDatabaseHas('tasks', [
            'title' => $taskData['title'],
            'user_id' => $this->user->id
        ]);
    }

    /** @test */
    public function can_get_single_task()
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->getJson("/api/tasks/{$task->id}");
        
        $response->assertStatus(200)
                 ->assertJson([
                     'data' => [
                         'id' => $task->id,
                         'title' => $task->title
                     ]
                 ]);
    }

    /** @test */
    public function can_update_task()
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);
        
        $updateData = [
            'title' => 'Updated task title',
            'status' => 'in_progress'
        ];
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->putJson("/api/tasks/{$task->id}", $updateData);
        
        $response->assertStatus(200)
                 ->assertJson([
                     'data' => [
                         'title' => $updateData['title'],
                         'status' => $updateData['status']
                     ]
                 ]);
    }

    /** @test */
    public function can_delete_task()
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->deleteJson("/api/tasks/{$task->id}");
        
        $response->assertStatus(204);
        
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }
}