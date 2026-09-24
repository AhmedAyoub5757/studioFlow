<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\TimeLog;
use App\Models\User;

class TimeLogPolicy
{
    /** Anyone assigned to the task can log time against it */
    public function create(User $user, Task $task): bool
    {
        if (! $user->can('permission', 'time_logs.create')) {
            return false;
        }

        return $task->assigned_to === $user->id;
    }

    /** View all logs on a project — PM/Agency Manager only */
    public function viewAnyForProject(User $user, \App\Models\Project $project): bool
    {
        if (! $user->can('permission', 'time_logs.view_any')) {
            return false;
        }

        return $user->can('permission', 'projects.view_any') || $project->isManagedBy($user);
    }

    /** A user can always see their own logged time */
    public function view(User $user, TimeLog $timeLog): bool
    {
        return $timeLog->user_id === $user->id
            || $this->viewAnyForProject($user, $timeLog->task->project);
    }
}
