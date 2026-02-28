<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(\App\Models\Task $task)
    {
        $this->authorizeAccess($task->project);
        return \App\Http\Resources\CommentResource::collection($task->comments()->with('user')->latest()->get());
    }

    public function store(Request $request, \App\Models\Task $task)
    {
        $this->authorizeAccess($task->project);

        $validated = $request->validate([
            'body' => 'required|string',
        ]);

        $comment = $task->comments()->create([
            'body' => $validated['body'],
            'user_id' => $request->user()->id,
        ]);

        return new \App\Http\Resources\CommentResource($comment->load('user'));
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
