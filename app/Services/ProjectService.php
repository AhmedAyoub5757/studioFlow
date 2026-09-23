<?php 

namespace App\Services;

use App\Models\Project;
use App\Models\User;

class ProjectService
{
    public function create(array $data, User $creator): Project
    {
        $project = Project::create([
            ...$data,
            'created_by' => $creator->id,
        ]);

        // Whoever creates the project is auto-assigned as manager if they hold
        // a PM/Agency Manager system role — keeps them able to act on it immediately.
        if ($creator->hasRole('project_manager') || $creator->hasRole('agency_manager')) {
            $project->staff()->attach($creator->id, ['role_on_project' => 'manager']);
        }

        return $project;
    }

    public function assignStaff(Project $project, int $userId, string $roleOnProject): void
    {
        $project->staff()->syncWithoutDetaching([
            $userId => ['role_on_project' => $roleOnProject],
        ]);
    }

    public function unassignStaff(Project $project, int $userId, string $roleOnProject): void
    {
        $project->staff()->wherePivot('role_on_project', $roleOnProject)->detach($userId);
    }

    /** Scoped query per role — used by controller@index */
    public function visibleTo(User $user)
    {
        if ($user->can('permission', 'projects.view_any')) {
            return Project::query();
        }

        return Project::query()->where(function ($q) use ($user) {
            $q->where('client_id', $user->id)
              ->orWhereHas('staff', fn ($sub) => $sub->where('users.id', $user->id));
        });
    }
}