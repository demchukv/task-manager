<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, \App\Models\Project $project)
    {
        $this->authorizeAccess($project);

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

        return \App\Http\Resources\TaskResource::collection($query->paginate(10));
    }

    public function store(Request $request, \App\Models\Project $project)
    {
        $this->authorizeAccess($project);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|in:todo,in_progress,done',
            'priority' => 'nullable|in:low,medium,high',
            'assignee_id' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
        ]);

        $task = $project->tasks()->create($validated);

        return new \App\Http\Resources\TaskResource($task);
    }

    public function show(\App\Models\Task $task)
    {
        $this->authorizeAccess($task->project);
        return new \App\Http\Resources\TaskResource($task->load('assignee'));
    }

    public function update(Request $request, \App\Models\Task $task)
    {
        $this->authorizeAccess($task->project);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|required|in:todo,in_progress,done',
            'priority' => 'sometimes|required|in:low,medium,high',
            'assignee_id' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
        ]);

        $task->update($validated);

        return new \App\Http\Resources\TaskResource($task);
    }

    public function destroy(\App\Models\Task $task)
    {
        $this->authorizeAccess($task->project);
        $task->delete();
        return response()->noContent();
    }

    protected function authorizeAccess(\App\Models\Project $project)
    {
        if (request()->user()->role === 'admin')
            return;

        if ($project->owner_id !== request()->user()->id && !$project->members->contains(request()->user()->id)) {
            abort(403);
        }
    }
}
