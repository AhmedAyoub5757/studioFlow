<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use App\Models\Project;

class TaskPolicy
{
    public function view(User $user, Task $task): bool
    {
        return $user->can('view', $task->project);
    }

    public function create(User $user, Project $project): bool
    {
        if (! $user->can('permission', 'tasks.create')) {
            return false;
        }

        return $user->can('permission', 'projects.view_any') || $project->isManagedBy($user);
    }

    /** Full edit: title, priority, reassignment, due date — PM/Agency Manager only */
    public function update(User $user, Task $task): bool
    {
        if (! $user->can('permission', 'tasks.update')) {
            return false;
        }

        return $user->can('permission', 'projects.view_any') || $task->project->isManagedBy($user);
    }

    /**
     * Narrow permission: the assignee can move their OWN task through the status
     * pipeline (todo -> in_progress -> in_review -> done), but cannot touch
     * anything else about the task. PM/Agency Manager can also do this via
     * the broader `update` ability, so this is really scoped for staff.
     */
    public function updateStatus(User $user, Task $task): bool
    {
        if ($this->update($user, $task)) {
            return true; // PM/Agency Manager already covered by full update
        }

        if (! $user->can('permission', 'tasks.update_status')) {
            return false;
        }

        return $task->assigned_to === $user->id;
    }

    public function delete(User $user, Task $task): bool
    {
        if (! $user->can('permission', 'tasks.delete')) {
            return false;
        }

        return $user->can('permission', 'projects.view_any') || $task->project->isManagedBy($user);
    }
}
