<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTimeLogRequest;
use App\Http\Resources\TimeLogResource;
use App\Models\Task;
use App\Services\TimeLogService;

class TimeLogController extends Controller
{
    /**
 * @OA\Get(
 *     path="/tasks/{task}/time-logs",
 *     tags={"TimeLogs"}, summary="List time logs for a task", security={{"sanctum":{}}},
 *     @OA\Parameter(name="task", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\Response(response=200, description="List")
 * )
 */
    protected TimeLogService $timeLogs;

    public function __construct(TimeLogService $timeLogs)
    {
        $this->timeLogs = $timeLogs;
    }

    public function index(Task $task)
    {
        $this->authorize('view', $task);

        $logs = $task->timeLogs()->with('user')->latest('log_date')->get();

        return TimeLogResource::collection($logs);
    }

    /**
 * @OA\Post(
 *     path="/tasks/{task}/time-logs",
 *     tags={"TimeLogs"}, summary="Log time against a task (assignee only)", security={{"sanctum":{}}},
 *     @OA\Parameter(name="task", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\RequestBody(required=true, @OA\JsonContent(
 *         required={"hours","log_date"},
 *         @OA\Property(property="hours", type="number", format="float", example=2.5),
 *         @OA\Property(property="log_date", type="string", format="date"),
 *         @OA\Property(property="description", type="string")
 *     )),
 *     @OA\Response(response=201, description="Logged"),
 *     @OA\Response(response=403, description="Not the assigned user for this task")
 * )
 */

    public function store(StoreTimeLogRequest $request, Task $task)
    {
        $log = $this->timeLogs->log($task, $request->validated(), $request->user());

        return new TimeLogResource($log->load('user'));
    }
}
