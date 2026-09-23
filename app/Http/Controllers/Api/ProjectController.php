<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssignStaffRequest;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Services\ProjectService;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    protected ProjectService $projects;

    public function __construct(ProjectService $projects)
    {
        $this->projects = $projects;
    }

    /**
     * @OA\Get(
     *     path="/api/projects",
     *     tags={"Projects"},
     *     summary="List projects visible to the authenticated user",
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Paginated list of projects")
     * )
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Project::class);

        $projects = $this->projects->visibleTo($request->user())
            ->with(['client', 'creator'])
            ->latest()
            ->paginate(15);

        return ProjectResource::collection($projects);
    }

    public function store(StoreProjectRequest $request)
    {
        $project = $this->projects->create($request->validated(), $request->user());

        return new ProjectResource($project->load(['client', 'creator', 'staff']));
    }

    public function show(Project $project)
    {
        $this->authorize('view', $project);

        return new ProjectResource($project->load(['client', 'creator', 'staff']));
    }

    public function update(UpdateProjectRequest $request, Project $project)
    {
        $project->update($request->validated());

        return new ProjectResource($project->load(['client', 'creator', 'staff']));
    }

    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);

        $project->delete();

        return response()->json(['message' => 'Project deleted'], 200);
    }

    public function assignStaff(AssignStaffRequest $request, Project $project)
    {
        $this->projects->assignStaff(
            $project,
            $request->user_id,
            $request->role_on_project
        );

        return new ProjectResource($project->load('staff'));
    }
}