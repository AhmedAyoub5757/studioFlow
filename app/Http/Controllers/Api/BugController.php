<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBugRequest;
use App\Http\Requests\TransitionBugRequest;
use App\Http\Requests\UpdateBugRequest;
use App\Http\Resources\BugResource;
use App\Models\Bug;
use App\Models\Project;
use App\Services\BugService;

class BugController extends Controller
{
    /**
     * @OA\Get(
     *     path="/projects/{project}/bugs",
     *     tags={"Bugs"}, summary="List bugs (QA sees all, Developer sees only assigned)", security={{"sanctum":{}}},
     *     @OA\Parameter(name="project", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="List", @OA\JsonContent(@OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Bug"))))
     * )
     */
    protected BugService $bugs;

    public function __construct(BugService $bugs)
    {
        $this->bugs = $bugs;
    }

    public function index(Project $project)
    {
        $this->authorize('view', $project);

        $bugs = $this->bugs->visibleFor($project, request()->user())
            ->with(['reporter', 'assignee'])
            ->latest()
            ->paginate(20);

        return BugResource::collection($bugs);
    }
    /**
     * @OA\Post(
     *     path="/projects/{project}/bugs",
     *     tags={"Bugs"}, summary="Report a bug (QA / PM / Agency Manager)", security={{"sanctum":{}}},
     *     @OA\Parameter(name="project", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"title","description","severity"},
     *         @OA\Property(property="title", type="string"),
     *         @OA\Property(property="description", type="string"),
     *         @OA\Property(property="severity", type="string", enum={"low","medium","high","critical"}),
     *         @OA\Property(property="assigned_to", type="integer", nullable=true),
     *         @OA\Property(property="task_id", type="integer", nullable=true)
     *     )),
     *     @OA\Response(response=201, description="Bug created with status=open", @OA\JsonContent(ref="#/components/schemas/Bug"))
     * )
     */
    public function store(StoreBugRequest $request, Project $project)
    {
        $bug = $this->bugs->create($project, $request->validated(), $request->user());

        return new BugResource($bug->load(['reporter', 'assignee']));
    }
    /**
     * @OA\Get(
     *     path="/bugs/{bug}",
     *     tags={"Bugs"}, summary="View a bug", security={{"sanctum":{}}},
     *     @OA\Parameter(name="bug", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Detail", @OA\JsonContent(ref="#/components/schemas/Bug"))
     * )
     */

    public function show(Bug $bug)
    {
        $this->authorize('view', $bug);

        return new BugResource($bug->load(['reporter', 'assignee']));
    }
    /**
     * @OA\Put(
     *     path="/bugs/{bug}",
     *     tags={"Bugs"}, summary="Full edit of a bug (PM / Agency Manager)", security={{"sanctum":{}}},
     *     @OA\Parameter(name="bug", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Updated", @OA\JsonContent(ref="#/components/schemas/Bug"))
     * )
     */
    public function update(UpdateBugRequest $request, Bug $bug)
    {
        $bug->update($request->validated());

        return new BugResource($bug->load(['reporter', 'assignee']));
    }
    /**
     * @OA\Patch(
     *     path="/bugs/{bug}/status",
     *     tags={"Bugs"},
     *     summary="Transition bug status (dev moves open→in_progress→fixed; QA moves fixed→closed/open; PM sets wont_fix)",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="bug", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"status"},
     *         @OA\Property(property="status", type="string", enum={"open","in_progress","fixed","closed","wont_fix"}),
     *         @OA\Property(property="note", type="string", nullable=true)
     *     )),
     *     @OA\Response(response=200, description="Transitioned", @OA\JsonContent(ref="#/components/schemas/Bug")),
     *     @OA\Response(response=422, description="Illegal transition for current status (state machine rule)"),
     *     @OA\Response(response=403, description="Role not authorized for this transition type")
     * )
     */
    public function transition(TransitionBugRequest $request, Bug $bug)
    {
        $bug = $this->bugs->transition($bug, $request->status, $request->note);

        return new BugResource($bug->load(['reporter', 'assignee']));
    }
    /**
     * @OA\Delete(
     *     path="/bugs/{bug}",
     *     tags={"Bugs"}, summary="Delete a bug", security={{"sanctum":{}}},
     *     @OA\Parameter(name="bug", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Deleted")
     * )
     */
    public function destroy(Bug $bug)
    {
        $this->authorize('delete', $bug);

        $bug->delete();

        return response()->json(['message' => 'Bug deleted']);
    }
}
