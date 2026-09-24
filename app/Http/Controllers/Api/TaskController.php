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

    public function store(StoreTaskRequest $request, Project $project)
    {
        $task = $this->tasks->create($project, $request->validated(), $request->user());

        return new TaskResource($task->load(['assignee', 'creator']));
    }

    public function show(Task $task)
    {
        $this->authorize('view', $task);

        return new TaskResource($task->load(['assignee', 'creator', 'timeLogs']));
    }

    /** Full edit — PM/Agency Manager only, enforced in UpdateTaskRequest */
    public function update(UpdateTaskRequest $request, Task $task)
    {
        $task->update($request->validated());

        return new TaskResource($task->load(['assignee', 'creator']));
    }

    /** Narrow status-only transition — assignee or manager, enforced in UpdateTaskStatusRequest */
    public function updateStatus(UpdateTaskStatusRequest $request, Task $task)
    {
        $task->update(['status' => $request->status]);

        return new TaskResource($task->load(['assignee', 'creator']));
    }

    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);

        $task->delete();

        return response()->json(['message' => 'Task deleted']);
    }
}