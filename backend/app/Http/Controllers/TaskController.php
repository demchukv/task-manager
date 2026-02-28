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
    public function index(Request $request, Project $project)
    {
        Gate::authorize('view', $project);

        $query = $project->tasks()->with('assignee');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->has('assignee_id')) {
            $query->where('assignee_id', $request->assignee_id);
        }

        if ($request->has('q')) {
            $query->where('title', 'like', '%' . $request->q . '%');
        }

        return $this->successResponse(TaskResource::collection($query->paginate(10))->response()->getData(true));
    }

    public function store(StoreTaskRequest $request, Project $project)
    {
        Gate::authorize('update', $project);

        $validated = $request->validated();

        $task = $project->tasks()->create($validated);

        return $this->successResponse(new TaskResource($task), 'Task created successfully', 201);
    }

    public function show(Task $task)
    {
        Gate::authorize('view', $task);
        return $this->successResponse(new TaskResource($task->load('assignee')));
    }

    public function update(UpdateTaskRequest $request, Task $task)
    {
        Gate::authorize('update', $task);

        $validated = $request->validated();

        $task->update($validated);

        return $this->successResponse(new TaskResource($task), 'Task updated successfully');
    }

    public function destroy(Task $task)
    {
        Gate::authorize('delete', $task);
        $task->delete();
        return $this->successResponse(null, 'Task deleted successfully', 204);
    }

}
