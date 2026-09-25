<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Milestone;
use App\Models\Task;
use App\Services\CommentService;

class CommentController extends Controller
{
    protected CommentService $comments;

    public function __construct(CommentService $comments)
    {
        $this->comments = $comments;
    }

    public function indexForTask(Task $task)
    {
        $this->authorize('view', $task);

        return CommentResource::collection($task->comments()->with('user')->get());
    }

    public function storeForTask(StoreCommentRequest $request, Task $task)
    {
        $comment = $this->comments->create($task, $request->body, $request->user());

        return new CommentResource($comment->load('user'));
    }

    public function indexForMilestone(Milestone $milestone)
    {
        $this->authorize('view', $milestone);

        return CommentResource::collection($milestone->comments()->with('user')->get());
    }

    public function storeForMilestone(StoreCommentRequest $request, Milestone $milestone)
    {
        $comment = $this->comments->create($milestone, $request->body, $request->user());

        return new CommentResource($comment->load('user'));
    }

    public function destroy(Comment $comment)
    {
        $this->authorize('delete', $comment);

        $comment->delete();

        return response()->json(['message' => 'Comment deleted']);
    }
}