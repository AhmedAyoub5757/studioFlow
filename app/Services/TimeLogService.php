<?php

namespace App\Services;

use App\Models\Task;
use App\Models\TimeLog;
use App\Models\User;

class TimeLogService
{
    public function log(Task $task, array $data, User $user): TimeLog
    {
        return TimeLog::create([
            ...$data,
            'task_id' => $task->id,
            'user_id' => $user->id,
        ]);
    }
}

