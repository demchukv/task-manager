<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Models\Project;
use App\Http\Resources\ProjectResource;
use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Project::query();

        // Access control: admin sees all, member sees owned or shared
        if ($request->user()->role !== 'admin') {
            $query->where(function ($q) use ($request) {
                $q->where('owner_id', $request->user()->id)
                    ->orWhereHas('members', function ($q) use ($request) {
                        $q->where('users.id', $request->user()->id);
                    })
                    ->orWhereHas('tasks', function ($q) use ($request) {
                        $q->where('assignee_id', $request->user()->id);
                    });
            });
        }

        // Search
        if ($request->filled('q')) {
            $query->where('name', 'like', '%' . $request->q . '%');
        }

        // Sorting
        $sort = $request->get('sort', '-created_at');
        $direction = str_starts_with($sort, '-') ? 'desc' : 'asc';
        $column = ltrim($sort, '-');

        $allowedSorts = ['name', 'created_at'];
        if (in_array($column, $allowedSorts)) {
            $query->orderBy($column, $direction);
        }

        return $this->successResponse(ProjectResource::collection($query->with('owner')->withCount('tasks')->paginate(10))->response()->getData(true));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProjectRequest $request)
    {
        $validated = $request->validated();

        $project = $request->user()->projects()->create($validated);

        return $this->successResponse(new ProjectResource($project), 'Project created successfully', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        Gate::authorize('view', $project);

        return $this->successResponse(new ProjectResource($project->load('owner', 'members')));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProjectRequest $request, Project $project)
    {
        Gate::authorize('update', $project);

        $validated = $request->validated();

        $project->update($validated);

        return $this->successResponse(new ProjectResource($project), 'Project updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        Gate::authorize('delete', $project);

        $project->delete();

        return $this->successResponse(null, 'Project deleted successfully', 204);
    }

}
