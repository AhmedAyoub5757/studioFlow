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
    /**
     * @OA\Get(
     *     path="/tasks/{task}/comments",
     *     tags={"Comments"}, summary="List comments on a task", security={{"sanctum":{}}},
     *     @OA\Parameter(name="task", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="List")
     * )
     */
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

    /**
     * @OA\Post(
     *     path="/tasks/{task}/comments",
     *     tags={"Comments"}, summary="Comment on a task", security={{"sanctum":{}}},
     *     @OA\Parameter(name="task", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(required={"body"}, @OA\Property(property="body", type="string"))),
     *     @OA\Response(response=201, description="Created")
     * )
     */

    public function storeForTask(StoreCommentRequest $request, Task $task)
    {
        $comment = $this->comments->create($task, $request->body, $request->user());

        return new CommentResource($comment->load('user'));
    }

    /**
     * @OA\Get(
     *     path="/milestones/{milestone}/comments",
     *     tags={"Comments"}, summary="List comments on a milestone", security={{"sanctum":{}}},
     *     @OA\Parameter(name="milestone", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="List")
     * )
     */
    public function indexForMilestone(Milestone $milestone)
    {
        $this->authorize('view', $milestone);

        return CommentResource::collection($milestone->comments()->with('user')->get());
    }
    /**
     * @OA\Post(
     *     path="/milestones/{milestone}/comments",
     *     tags={"Comments"}, summary="Comment on a milestone", security={{"sanctum":{}}},
     *     @OA\Parameter(name="milestone", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(required={"body"}, @OA\Property(property="body", type="string"))),
     *     @OA\Response(response=201, description="Created")
     * )
     */
    public function storeForMilestone(StoreCommentRequest $request, Milestone $milestone)
    {
        $comment = $this->comments->create($milestone, $request->body, $request->user());

        return new CommentResource($comment->load('user'));
    }
    /**
     * @OA\Get(
     *     path="/bugs/{bug}/comments",
     *     tags={"Comments"}, summary="List comments on a bug", security={{"sanctum":{}}},
     *     @OA\Parameter(name="bug", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="List")
     * )
     */


    public function indexForBug(\App\Models\Bug $bug)
    {
        $this->authorize('view', $bug);

        return CommentResource::collection($bug->comments()->with('user')->get());
    }

    /**
     * @OA\Post(
     *     path="/bugs/{bug}/comments",
     *     tags={"Comments"}, summary="Comment on a bug", security={{"sanctum":{}}},
     *     @OA\Parameter(name="bug", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(required={"body"}, @OA\Property(property="body", type="string"))),
     *     @OA\Response(response=201, description="Created")
     * )
     */
    public function storeForBug(\App\Http\Requests\StoreCommentRequest $request, \App\Models\Bug $bug)
    {
        $comment = $this->comments->create($bug, $request->body, $request->user());

        return new CommentResource($comment->load('user'));
    }

    /**
     * @OA\Delete(
     *     path="/comments/{comment}",
     *     tags={"Comments"}, summary="Delete a comment (own comment, or moderation permission)", security={{"sanctum":{}}},
     *     @OA\Parameter(name="comment", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Deleted"),
     *     @OA\Response(response=403, description="Not the author and lacks comments.delete_any")
     * )
     */
    public function destroy(Comment $comment)
    {
        $this->authorize('delete', $comment);

        $comment->delete();

        return response()->json(['message' => 'Comment deleted']);
    }
}
