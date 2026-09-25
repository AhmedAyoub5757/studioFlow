<?php

namespace App\Policies;

use App\Models\Milestone;
use App\Models\User;

class ApprovalPolicy
{
    /** PM (manager on project) or Agency Manager requests approval */
    public function request(User $user, Milestone $milestone): bool
    {
        if (! $user->can('permission', 'approvals.request')) {
            return false;
        }

        return $user->can('permission', 'projects.view_any') || $milestone->project->isManagedBy($user);
    }

    /** Client who owns the project (or Agency Manager, as override) decides */
    public function decide(User $user, Milestone $milestone): bool
    {
        if (! $user->can('permission', 'approvals.decide')) {
            return false;
        }

        if ($user->can('permission', 'projects.view_any')) {
            return true; // Agency Manager override
        }

        return $milestone->project->client_id === $user->id;
    }
}
