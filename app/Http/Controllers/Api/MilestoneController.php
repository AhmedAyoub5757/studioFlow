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

    public function store(StoreMilestoneRequest $request, Project $project)
    {
        $milestone = $this->milestones->create($project, $request->validated());

        return new MilestoneResource($milestone);
    }

    public function show(Milestone $milestone)
    {
        $this->authorize('view', $milestone);

        return new MilestoneResource($milestone->loadCount('tasks'));
    }

    public function update(UpdateMilestoneRequest $request, Milestone $milestone)
    {
        $milestone->update($request->validated());

        return new MilestoneResource($milestone);
    }

    public function destroy(Milestone $milestone)
    {
        $this->authorize('delete', $milestone);

        $milestone->delete();

        return response()->json(['message' => 'Milestone deleted']);
    }
}
