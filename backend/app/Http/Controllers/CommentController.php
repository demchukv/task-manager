<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Models\Task;
use App\Http\Resources\CommentResource;
use App\Http\Requests\Comment\StoreCommentRequest;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    /**
     * @OA\Get(
     *     path="/tasks/{task_id}/comments",
     *     operationId="getCommentsList",
     *     tags={"Comments"},
     *     summary="Get list of comments for a task",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="task_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *     )
     * )
     */
    public function index(Task $task)
    {
        Gate::authorize('view', $task);
        return $this->successResponse(CommentResource::collection($task->comments()->with('user')->latest()->get()));
    }

    /**
     * @OA\Post(
     *     path="/tasks/{task_id}/comments",
     *     operationId="storeComment",
     *     tags={"Comments"},
     *     summary="Add a comment to a task",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="task_id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"body"},
     *             @OA\Property(property="body", type="string", example="Great job on this task!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Comment added successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Comment")
     *     )
     * )
     */
    public function store(StoreCommentRequest $request, Task $task)
    {
        Gate::authorize('view', $task);

        $validated = $request->validated();

        $comment = $task->comments()->create([
            'body' => $validated['body'],
            'user_id' => $request->user()->id,
        ]);

        return $this->successResponse(new CommentResource($comment->load('user')), 'Comment added successfully');
    }

}
