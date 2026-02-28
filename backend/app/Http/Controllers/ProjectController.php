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
    /**
     * @OA\Get(
     *     path="/projects",
     *     operationId="getProjectsList",
     *     tags={"Projects"},
     *     summary="Get list of projects",
     *     description="Returns list of projects",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="q",
     *         in="query",
     *         description="Search query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *     ),
     * )
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
    /**
     * @OA\Post(
     *     path="/projects",
     *     operationId="storeProject",
     *     tags={"Projects"},
     *     summary="Create new project",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="New Project"),
     *             @OA\Property(property="description", type="string", example="Project description")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Project created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Project")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *     )
     * )
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
    /**
     * @OA\Get(
     *     path="/projects/{id}",
     *     operationId="getProjectById",
     *     tags={"Projects"},
     *     summary="Get project details",
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
     *         @OA\JsonContent(ref="#/components/schemas/Project")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Project not found",
     *     )
     * )
     */
    public function show(Project $project)
    {
        Gate::authorize('view', $project);

        return $this->successResponse(new ProjectResource($project->load('owner', 'members')));
    }

    /**
     * Update the specified resource in storage.
     */
    /**
     * @OA\Put(
     *     path="/projects/{id}",
     *     operationId="updateProject",
     *     tags={"Projects"},
     *     summary="Update existing project",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Updated Project Name"),
     *             @OA\Property(property="description", type="string", example="Updated description")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Project updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Project")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Project not found",
     *     )
     * )
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
    /**
     * @OA\Delete(
     *     path="/projects/{id}",
     *     operationId="deleteProject",
     *     tags={"Projects"},
     *     summary="Delete project",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Project deleted successfully",
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Project not found",
     *     )
     * )
     */
    public function destroy(Project $project)
    {
        Gate::authorize('delete', $project);

        $project->delete();

        return $this->successResponse(null, 'Project deleted successfully', 204);
    }

}
