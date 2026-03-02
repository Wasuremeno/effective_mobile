<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TaskApiTest extends TestCase
{
    use RefreshDatabase;

// Test GET /api/tasks
    
    public function test_can_get_all_tasks(): void
    {
        Task::factory()->count(3)->create();

        $response = $this->getJson('/api/tasks');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => ['id', 'title', 'description', 'status', 'created_at', 'updated_at']
                ]
            ]);
    }

//  Test POST /api/tasks
     
    public function test_can_create_task(): void
    {
        $taskData = [
            'title' => 'Test Task',
            'description' => 'Test Description',
            'status' => 'pending'
        ];

        $response = $this->postJson('/api/tasks', $taskData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Task created successfully'
            ])
            ->assertJsonPath('data.title', 'Test Task');

        $this->assertDatabaseHas('tasks', $taskData);
    }

// Test POST /api/tasks with validation error
  
    public function test_cannot_create_task_without_title(): void
    {
        $response = $this->postJson('/api/tasks', [
            'description' => 'Test Description'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title']);
    }

    // Test GET /api/tasks/{id}
     
    public function test_can_get_single_task(): void
    {
        $task = Task::factory()->create();

        $response = $this->getJson("/api/tasks/{$task->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $task->id,
                    'title' => $task->title
                ]
            ]);
    }

//  Test GET /api/tasks/{id} with non-existent task
     
    public function test_cannot_get_non_existent_task(): void
    {
        $response = $this->getJson('/api/tasks/99999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Task not found'
            ]);
    }

//   Test PUT /api/tasks/{id}
     
    public function test_can_update_task(): void
    {
        $task = Task::factory()->create();
        
        $updatedData = [
            'title' => 'Updated Title',
            'status' => 'completed'
        ];

        $response = $this->putJson("/api/tasks/{$task->id}", $updatedData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Task updated successfully'
            ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Updated Title',
            'status' => 'completed'
        ]);
    }

    //   Test DELETE /api/tasks/{id}
   
    public function test_can_delete_task(): void
    {
        $task = Task::factory()->create();

        $response = $this->deleteJson("/api/tasks/{$task->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Task deleted successfully'
            ]);

        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }
}