<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = \App\Models\Project::query();

        // Access control: admin sees all, member sees owned or shared
        if ($request->user()->role !== 'admin') {
            $query->where(function ($q) use ($request) {
                $q->where('owner_id', $request->user()->id)
                    ->orWhereHas('members', function ($q) use ($request) {
                        $q->where('users.id', $request->user()->id);
                    });
            });
        }

        // Search
        if ($request->has('q')) {
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

        return \App\Http\Resources\ProjectResource::collection($query->paginate(10));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $project = $request->user()->projects()->create($validated);

        return new \App\Http\Resources\ProjectResource($project);
    }

    /**
     * Display the specified resource.
     */
    public function show(\App\Models\Project $project)
    {
        // Policy check would be better, but implementing direct check for now
        $this->authorizeAccess($project);

        return new \App\Http\Resources\ProjectResource($project->load('owner', 'members'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, \App\Models\Project $project)
    {
        $this->authorizeOwner($project);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $project->update($validated);

        return new \App\Http\Resources\ProjectResource($project);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(\App\Models\Project $project)
    {
        $this->authorizeOwner($project);

        $project->delete();

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

    protected function authorizeOwner(\App\Models\Project $project)
    {
        if (request()->user()->role === 'admin')
            return;

        if ($project->owner_id !== request()->user()->id) {
            abort(403);
        }
    }
}
