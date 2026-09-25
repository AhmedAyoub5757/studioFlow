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
    /**
     * @OA\Get(
     *     path="/projects",
     *     tags={"Projects"},
     *     summary="List projects visible to the authenticated user",
     *     description="Agency Manager sees all projects. Others see only projects they own (Client) or are staffed on.",
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Paginated list",
     *         @OA\JsonContent(@OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Project")))
     *     )
     * )
     */
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

    /**
     * @OA\Post(
     *     path="/projects",
     *     tags={"Projects"},
     *     summary="Create a project (Agency Manager / Project Manager)",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"name","client_id"},
     *         @OA\Property(property="name", type="string"),
     *         @OA\Property(property="description", type="string"),
     *         @OA\Property(property="client_id", type="integer"),
     *         @OA\Property(property="budget", type="number"),
     *         @OA\Property(property="start_date", type="string", format="date"),
     *         @OA\Property(property="end_date", type="string", format="date")
     *     )),
     *     @OA\Response(response=201, description="Project created", @OA\JsonContent(ref="#/components/schemas/Project")),
     *     @OA\Response(response=403, description="Missing projects.create permission")
     * )
     */

    public function store(StoreProjectRequest $request)
    {
        $project = $this->projects->create($request->validated(), $request->user());

        return new ProjectResource($project->load(['client', 'creator', 'staff']));
    }

    /**
     * @OA\Get(
     *     path="/projects/{project}",
     *     tags={"Projects"},
     *     summary="View a single project",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="project", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Project detail", @OA\JsonContent(ref="#/components/schemas/Project")),
     *     @OA\Response(response=403, description="Not owner, not assigned, not Agency Manager")
     * )
     */

    public function show(Project $project)
    {
        $this->authorize('view', $project);

        return new ProjectResource($project->load(['client', 'creator', 'staff']));
    }

    /**
     * @OA\Put(
     *     path="/projects/{project}",
     *     tags={"Projects"},
     *     summary="Update a project (owning PM or Agency Manager only)",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="project", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(@OA\JsonContent(
     *         @OA\Property(property="status", type="string", enum={"pending","in_progress","on_hold","completed","cancelled"}),
     *         @OA\Property(property="budget", type="number")
     *     )),
     *     @OA\Response(response=200, description="Updated", @OA\JsonContent(ref="#/components/schemas/Project")),
     *     @OA\Response(response=403, description="PM not managing this project")
     * )
     */

    public function update(UpdateProjectRequest $request, Project $project)
    {
        $project->update($request->validated());

        return new ProjectResource($project->load(['client', 'creator', 'staff']));
    }

    /**
     * @OA\Delete(
     *     path="/projects/{project}",
     *     tags={"Projects"},
     *     summary="Delete a project (Agency Manager / Super Admin only)",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="project", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Deleted"),
     *     @OA\Response(response=403, description="Even the managing PM cannot delete")
     * )
     */

    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);

        $project->delete();

        return response()->json(['message' => 'Project deleted'], 200);
    }
    /**
     * @OA\Post(
     *     path="/projects/{project}/assign",
     *     tags={"Projects"},
     *     summary="Assign a staff member to the project",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="project", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"user_id","role_on_project"},
     *         @OA\Property(property="user_id", type="integer"),
     *         @OA\Property(property="role_on_project", type="string", enum={"manager","developer","designer","qa"})
     *     )),
     *     @OA\Response(response=200, description="Staff assigned", @OA\JsonContent(ref="#/components/schemas/Project")),
     *     @OA\Response(response=422, description="System role doesn't match role_on_project", @OA\JsonContent(ref="#/components/schemas/ValidationError"))
     * )
     */
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
