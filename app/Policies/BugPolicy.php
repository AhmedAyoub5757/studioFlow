<?php

namespace App\Policies;

use App\Models\Bug;
use App\Models\Project;
use App\Models\User;

class BugPolicy
{
    public function view(User $user, Bug $bug): bool
    {
        return $user->can('view', $bug->project);
    }

    public function create(User $user, Project $project): bool
    {
        if (! $user->can('permission', 'bugs.create')) {
            return false;
        }

        return $user->can('permission', 'projects.view_any')
            || $project->isManagedBy($user)
            || $project->staff()->where('user_id', $user->id)->exists(); // any staff, incl. QA
    }

    /** Full edit: reassign, change severity/description — PM/Agency Manager only */
    public function update(User $user, Bug $bug): bool
    {
        if (! $user->can('permission', 'bugs.update')) {
            return false;
        }

        return $user->can('permission', 'projects.view_any') || $bug->project->isManagedBy($user);
    }

    public function delete(User $user, Bug $bug): bool
    {
        if (! $user->can('permission', 'bugs.delete')) {
            return false;
        }

        return $user->can('permission', 'projects.view_any') || $bug->project->isManagedBy($user);
    }

    /** Developer moving their own bug: open->in_progress, in_progress->fixed */
    public function transitionAsDeveloper(User $user, Bug $bug): bool
    {
        if ($this->update($user, $bug)) {
            return true; // PM/Agency Manager can also drive this transition
        }

        if (! $user->can('permission', 'bugs.transition_dev')) {
            return false;
        }

        return $bug->assigned_to === $user->id;
    }

    /** QA verifying: fixed->closed, fixed->open (reopen) */
    public function transitionAsQa(User $user, Bug $bug): bool
    {
        if ($this->update($user, $bug)) {
            return true; // PM/Agency Manager override
        }

        if (! $user->can('permission', 'bugs.transition_qa')) {
            return false;
        }

        // Any QA on the project can verify — not just the original reporter,
        // since QA often works as a pool, not 1:1 with bugs they filed.
        return $bug->project->staffWithRole('qa')->where('user_id', $user->id)->exists();
    }

    public function markWontFix(User $user, Bug $bug): bool
    {
        if (! $user->can('permission', 'bugs.wont_fix')) {
            return false;
        }

        return $user->can('permission', 'projects.view_any') || $bug->project->isManagedBy($user);
    }
}