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

    public function store(StoreBugRequest $request, Project $project)
    {
        $bug = $this->bugs->create($project, $request->validated(), $request->user());

        return new BugResource($bug->load(['reporter', 'assignee']));
    }

    public function show(Bug $bug)
    {
        $this->authorize('view', $bug);

        return new BugResource($bug->load(['reporter', 'assignee']));
    }

    public function update(UpdateBugRequest $request, Bug $bug)
    {
        $bug->update($request->validated());

        return new BugResource($bug->load(['reporter', 'assignee']));
    }

    public function transition(TransitionBugRequest $request, Bug $bug)
    {
        $bug = $this->bugs->transition($bug, $request->status, $request->note);

        return new BugResource($bug->load(['reporter', 'assignee']));
    }

    public function destroy(Bug $bug)
    {
        $this->authorize('delete', $bug);

        $bug->delete();

        return response()->json(['message' => 'Bug deleted']);
    }
}