<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTimeLogRequest;
use App\Http\Resources\TimeLogResource;
use App\Models\Task;
use App\Services\TimeLogService;

class TimeLogController extends Controller
{
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

    public function store(StoreTimeLogRequest $request, Task $task)
    {
        $log = $this->timeLogs->log($task, $request->validated(), $request->user());

        return new TimeLogResource($log->load('user'));
    }
}
