<?php

namespace App\Policies;

use App\Models\Milestone;
use App\Models\User;

class MilestonePolicy
{
    public function view(User $user, Milestone $milestone): bool
    {
        return $user->can('view', $milestone->project); // reuse Sprint 1 project visibility
    }

    public function create(User $user, \App\Models\Project $project): bool
    {
        if (! $user->can('permission', 'milestones.create')) {
            return false;
        }

        return $user->can('permission', 'projects.view_any') || $project->isManagedBy($user);
    }

    public function update(User $user, Milestone $milestone): bool
    {
        if (! $user->can('permission', 'milestones.update')) {
            return false;
        }

        return $user->can('permission', 'projects.view_any') || $milestone->project->isManagedBy($user);
    }

    public function delete(User $user, Milestone $milestone): bool
    {
        if (! $user->can('permission', 'milestones.delete')) {
            return false;
        }

        return $user->can('permission', 'projects.view_any') || $milestone->project->isManagedBy($user);
    }
}
