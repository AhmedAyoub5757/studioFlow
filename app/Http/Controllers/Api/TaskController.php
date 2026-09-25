<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Requests\UpdateTaskStatusRequest;
use App\Http\Resources\TaskResource;
use App\Models\Project;
use App\Models\Task;
use App\Services\TaskService;

class TaskController extends Controller
{
    /**
     * @OA\Get(
     *     path="/projects/{project}/tasks",
     *     tags={"Tasks"}, summary="List tasks (managers see all, staff see only their own assigned tasks)",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="project", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="List", @OA\JsonContent(@OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Task"))))
     * )
     */
    protected TaskService $tasks;

    public function __construct(TaskService $tasks)
    {
        $this->tasks = $tasks;
    }

    public function index(Project $project)
    {
        $this->authorize('view', $project);

        $tasks = $this->tasks->visibleFor($project, request()->user())
            ->with(['assignee', 'creator'])
            ->latest()
            ->paginate(20);

        return TaskResource::collection($tasks);
    }
    /**
     * @OA\Post(
     *     path="/projects/{project}/tasks",
     *     tags={"Tasks"}, summary="Create a task", security={{"sanctum":{}}},
     *     @OA\Parameter(name="project", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"title","type"},
     *         @OA\Property(property="title", type="string"),
     *         @OA\Property(property="milestone_id", type="integer", nullable=true),
     *         @OA\Property(property="assigned_to", type="integer", nullable=true),
     *         @OA\Property(property="type", type="string", enum={"development","design","qa","general"}),
     *         @OA\Property(property="priority", type="string", enum={"low","medium","high","urgent"})
     *     )),
     *     @OA\Response(response=201, description="Created", @OA\JsonContent(ref="#/components/schemas/Task")),
     *     @OA\Response(response=422, description="assigned_to not on project staff, or milestone from different project")
     * )
     */
    public function store(StoreTaskRequest $request, Project $project)
    {
        $task = $this->tasks->create($project, $request->validated(), $request->user());

        return new TaskResource($task->load(['assignee', 'creator']));
    }
    /**
     * @OA\Get(
     *     path="/tasks/{task}",
     *     tags={"Tasks"}, summary="View a task", security={{"sanctum":{}}},
     *     @OA\Parameter(name="task", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Detail", @OA\JsonContent(ref="#/components/schemas/Task"))
     * )
     */
    public function show(Task $task)
    {
        $this->authorize('view', $task);

        return new TaskResource($task->load(['assignee', 'creator', 'timeLogs']));
    }
    /**
     * @OA\Put(
     *     path="/tasks/{task}",
     *     tags={"Tasks"}, summary="Full edit of a task (PM / Agency Manager only)", security={{"sanctum":{}}},
     *     @OA\Parameter(name="task", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(@OA\JsonContent(
     *         @OA\Property(property="title", type="string"),
     *         @OA\Property(property="assigned_to", type="integer"),
     *         @OA\Property(property="priority", type="string")
     *     )),
     *     @OA\Response(response=200, description="Updated", @OA\JsonContent(ref="#/components/schemas/Task")),
     *     @OA\Response(response=403, description="Developer/Designer/QA cannot full-edit, even their own task")
     * )
     */
    /** Full edit — PM/Agency Manager only, enforced in UpdateTaskRequest */
    public function update(UpdateTaskRequest $request, Task $task)
    {
        $task->update($request->validated());

        return new TaskResource($task->load(['assignee', 'creator']));
    }

    /**
     * @OA\Patch(
     *     path="/tasks/{task}/status",
     *     tags={"Tasks"}, summary="Update task status (assignee, or PM/Agency Manager)", security={{"sanctum":{}}},
     *     @OA\Parameter(name="task", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"status"},
     *         @OA\Property(property="status", type="string", enum={"todo","in_progress","in_review","done"})
     *     )),
     *     @OA\Response(response=200, description="Status updated", @OA\JsonContent(ref="#/components/schemas/Task")),
     *     @OA\Response(response=403, description="Not the task's assignee")
     * )
     */
    /** Narrow status-only transition — assignee or manager, enforced in UpdateTaskStatusRequest */
    public function updateStatus(UpdateTaskStatusRequest $request, Task $task)
    {
        $task->update(['status' => $request->status]);

        return new TaskResource($task->load(['assignee', 'creator']));
    }
    /**
     * @OA\Delete(
     *     path="/tasks/{task}",
     *     tags={"Tasks"}, summary="Delete a task", security={{"sanctum":{}}},
     *     @OA\Parameter(name="task", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Deleted")
     * )
     */
    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);

        $task->delete();

        return response()->json(['message' => 'Task deleted']);
    }
}
