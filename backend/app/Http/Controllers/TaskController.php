<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Models\Project;
use App\Models\Task;
use App\Http\Resources\TaskResource;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    /**
     * @OA\Get(
     *     path="/projects/{project_id}/tasks",
     *     operationId="getTasksList",
     *     tags={"Tasks"},
     *     summary="Get list of tasks for a project",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="project_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by status",
     *         required=false,
     *         @OA\Schema(type="string", enum={"todo", "in_progress", "done"})
     *     ),
     *     @OA\Parameter(
     *         name="priority",
     *         in="query",
     *         description="Filter by priority",
     *         required=false,
     *         @OA\Schema(type="string", enum={"low", "medium", "high"})
     *     ),
     *     @OA\Parameter(
     *         name="q",
     *         in="query",
     *         description="Search by title",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *     )
     * )
     */
    public function index(Request $request, Project $project)
    {
        Gate::authorize('view', $project);

        $query = $project->tasks()->with('assignee');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('assignee_id')) {
            $query->where('assignee_id', $request->assignee_id);
        }

        if ($request->filled('q')) {
            $query->where('title', 'like', '%' . $request->q . '%');
        }

        return $this->successResponse(TaskResource::collection($query->paginate(10))->response()->getData(true));
    }

    /**
     * @OA\Post(
     *     path="/projects/{project_id}/tasks",
     *     operationId="storeTask",
     *     tags={"Tasks"},
     *     summary="Create new task in project",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="project_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title","status","priority"},
     *             @OA\Property(property="title", type="string", example="New Task"),
     *             @OA\Property(property="description", type="string", example="Task description"),
     *             @OA\Property(property="status", type="string", enum={"todo", "in_progress", "done"}),
     *             @OA\Property(property="priority", type="string", enum={"low", "medium", "high"}),
     *             @OA\Property(property="due_date", type="string", format="date", example="2023-12-31"),
     *             @OA\Property(property="assignee_id", type="integer", description="Only for admins")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Task created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Task")
     *     )
     * )
     */
    public function store(StoreTaskRequest $request, Project $project)
    {
        Gate::authorize('update', $project);

        $validated = $request->validated();

        if ($request->user()->role !== 'admin') {
            $validated['assignee_id'] = $request->user()->id;
        }

        $task = $project->tasks()->create($validated);

        return $this->successResponse(new TaskResource($task), 'Task created successfully', 201);
    }

    /**
     * @OA\Get(
     *     path="/tasks/{id}",
     *     operationId="getTaskById",
     *     tags={"Tasks"},
     *     summary="Get task details",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Task")
     *     )
     * )
     */
    public function show(Task $task)
    {
        Gate::authorize('view', $task);
        return $this->successResponse(new TaskResource($task->load(['assignee', 'project.owner'])));
    }

    /**
     * @OA\Put(
     *     path="/tasks/{id}",
     *     operationId="updateTask",
     *     tags={"Tasks"},
     *     summary="Update task",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string"),
     *             @OA\Property(property="status", type="string", enum={"todo", "in_progress", "done"}),
     *             @OA\Property(property="priority", type="string", enum={"low", "medium", "high"}),
     *             @OA\Property(property="assignee_id", type="integer", description="Only for admins")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Task updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Task")
     *     )
     * )
     */
    public function update(UpdateTaskRequest $request, Task $task)
    {
        Gate::authorize('update', $task);

        $validated = $request->validated();

        if ($request->user()->role !== 'admin') {
            unset($validated['assignee_id']);
        }

        $task->update($validated);

        return $this->successResponse(new TaskResource($task), 'Task updated successfully');
    }

    /**
     * @OA\Delete(
     *     path="/tasks/{id}",
     *     operationId="deleteTask",
     *     tags={"Tasks"},
     *     summary="Delete task",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Task deleted successfully",
     *     )
     * )
     */
    public function destroy(Task $task)
    {
        Gate::authorize('delete', $task);
        $task->delete();
        return $this->successResponse(null, 'Task deleted successfully', 204);
    }

}
