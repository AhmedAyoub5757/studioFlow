<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;

class TaskService
{
    public function create(Project $project, array $data, User $creator): Task
    {
        return Task::create([
            ...$data,
            'project_id' => $project->id,
            'created_by' => $creator->id,
        ]);
    }

    /** Scoped query per role — reused pattern from ProjectService::visibleTo */
    public function visibleFor(Project $project, User $user)
    {
        $query = $project->tasks();

        $isManagerOrAbove = $user->can('permission', 'projects.view_any') || $project->isManagedBy($user);

        // Staff who aren't managers only see tasks assigned to them
        if (! $isManagerOrAbove && $project->staff()->where('user_id', $user->id)->exists()) {
            $query->where('assigned_to', $user->id);
        }

        // Client sees all tasks on their project (read-only, enforced by Policy elsewhere)
        return $query;
    }
}