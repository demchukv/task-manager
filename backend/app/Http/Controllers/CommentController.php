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
    public function index(Task $task)
    {
        Gate::authorize('view', $task);
        return $this->successResponse(CommentResource::collection($task->comments()->with('user')->latest()->get()));
    }

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
