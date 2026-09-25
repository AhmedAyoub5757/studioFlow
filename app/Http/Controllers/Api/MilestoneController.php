<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMilestoneRequest;
use App\Http\Requests\UpdateMilestoneRequest;
use App\Http\Resources\MilestoneResource;
use App\Models\Milestone;
use App\Models\Project;
use App\Services\MilestoneService;

class MilestoneController extends Controller
{
    /**
     * @OA\Get(
     *     path="/projects/{project}/milestones",
     *     tags={"Milestones"}, summary="List milestones for a project", security={{"sanctum":{}}},
     *     @OA\Parameter(name="project", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="List", @OA\JsonContent(@OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Milestone"))))
     * )
     */
    protected MilestoneService $milestones;

    public function __construct(MilestoneService $milestones)
    {
        $this->milestones = $milestones;
    }

    public function index(Project $project)
    {
        $this->authorize('view', $project);

        $milestones = $project->milestones()->withCount('tasks')->orderBy('order')->get();

        return MilestoneResource::collection($milestones);
    }

    /**
     * @OA\Post(
     *     path="/projects/{project}/milestones",
     *     tags={"Milestones"}, summary="Create a milestone (owning PM / Agency Manager)", security={{"sanctum":{}}},
     *     @OA\Parameter(name="project", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"title"},
     *         @OA\Property(property="title", type="string"),
     *         @OA\Property(property="amount", type="number"),
     *         @OA\Property(property="due_date", type="string", format="date")
     *     )),
     *     @OA\Response(response=201, description="Created", @OA\JsonContent(ref="#/components/schemas/Milestone"))
     * )
     */

    public function store(StoreMilestoneRequest $request, Project $project)
    {
        $milestone = $this->milestones->create($project, $request->validated());

        return new MilestoneResource($milestone);
    }
    /**
     * @OA\Get(
     *     path="/milestones/{milestone}",
     *     tags={"Milestones"}, summary="View a milestone", security={{"sanctum":{}}},
     *     @OA\Parameter(name="milestone", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Detail", @OA\JsonContent(ref="#/components/schemas/Milestone"))
     * )
     */
    public function show(Milestone $milestone)
    {
        $this->authorize('view', $milestone);

        return new MilestoneResource($milestone->loadCount('tasks'));
    }

    /**
     * @OA\Put(
     *     path="/milestones/{milestone}",
     *     tags={"Milestones"}, summary="Update a milestone", security={{"sanctum":{}}},
     *     @OA\Parameter(name="milestone", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(@OA\JsonContent(
     *         @OA\Property(property="status", type="string", enum={"pending","in_progress","completed","approved"})
     *     )),
     *     @OA\Response(response=200, description="Updated", @OA\JsonContent(ref="#/components/schemas/Milestone"))
     * )
     */
    public function update(UpdateMilestoneRequest $request, Milestone $milestone)
    {
        $milestone->update($request->validated());

        return new MilestoneResource($milestone);
    }

    /**
     * @OA\Delete(
     *     path="/milestones/{milestone}",
     *     tags={"Milestones"}, summary="Delete a milestone", security={{"sanctum":{}}},
     *     @OA\Parameter(name="milestone", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Deleted")
     * )
     */
    public function destroy(Milestone $milestone)
    {
        $this->authorize('delete', $milestone);

        $milestone->delete();

        return response()->json(['message' => 'Milestone deleted']);
    }
}
