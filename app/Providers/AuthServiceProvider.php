<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        \App\Models\Project::class => \App\Policies\ProjectPolicy::class,
        \App\Models\Milestone::class => \App\Policies\MilestonePolicy::class,
        \App\Models\Task::class => \App\Policies\TaskPolicy::class,
        \App\Models\TimeLog::class => \App\Policies\TimeLogPolicy::class,
        \App\Models\Comment::class => \App\Policies\CommentPolicy::class,
        \App\Models\Approval::class => \App\Policies\ApprovalPolicy::class,
    ];

    public function boot(): void
    {
        // Super Admin bypasses every permission check, everywhere.
        Gate::before(function (User $user, string $ability) {
            return $user->hasRole('super_admin') ? true : null;
        });

        // Generic permission gate: Gate::authorize('projects.create')
        Gate::define('permission', function (User $user, string $permissionName) {
            return $user->hasPermission($permissionName);
        });
    }
}
