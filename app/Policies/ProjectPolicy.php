<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    /**
     * Super Admin already bypasses everything via Gate::before (Sprint 0).
     * Agency Manager has projects.view_any -> sees all, checked in controller/index scoping.
     */

    public function viewAny(User $user): bool
    {
        // Everyone with a role can call the index endpoint — the CONTROLLER scopes
        // the query differently per role (Agency Manager sees all, others see only theirs).
        // We still gate it so an unauthenticated or roleless user can't even try.
        return $user->roles->isNotEmpty();
    }

    public function view(User $user, Project $project): bool
    {
        if ($user->can('permission', 'projects.view_any')) {
            return true;
        }

        if ($project->client_id === $user->id) {
            return true; // Client sees their own project
        }

        return $user->isAssignedTo($project); // staff sees only assigned projects
    }

    public function create(User $user): bool
    {
        return $user->can('permission', 'projects.create');
    }

    public function update(User $user, Project $project): bool
    {
        if (! $user->can('permission', 'projects.update')) {
            return false;
        }

        // Agency Manager can update any project. PM can only update
        // a project they are assigned to WITH the 'manager' role_on_project.
        if ($user->can('permission', 'projects.view_any')) {
            return true; // Agency Manager
        }

        return $project->staffWithRole('manager')
            ->where('user_id', $user->id)
            ->exists();
    }

    public function delete(User $user, Project $project): bool
    {
        // Deliberately NOT delegated to PM at all — only Super Admin (Gate::before)
        // and Agency Manager (via projects.delete) can delete a project.
        return $user->can('permission', 'projects.delete');
    }

    public function assignStaff(User $user, Project $project): bool
    {
        if (! $user->can('permission', 'projects.assign_staff')) {
            return false;
        }

        if ($user->can('permission', 'projects.view_any')) {
            return true; // Agency Manager
        }

        return $project->staffWithRole('manager')->where('user_id', $user->id)->exists();
    }
}
