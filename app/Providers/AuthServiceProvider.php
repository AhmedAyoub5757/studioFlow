<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        // We'll add Project::class => ProjectPolicy::class etc. in later sprints
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
