<?php

namespace App\Services;

use App\Models\Bug;
use App\Models\Project;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class BugService
{
    /** Valid current-status => allowed next-statuses map. The actual state machine. */
    private const TRANSITIONS = [
        'open' => ['in_progress', 'wont_fix'],
        'in_progress' => ['fixed', 'open', 'wont_fix'],
        'fixed' => ['closed', 'open', 'wont_fix'], // QA can close, or reopen if fix is bad
        'closed' => [], // terminal
        'wont_fix' => [], // terminal
    ];

    public function create(Project $project, array $data, User $reporter): Bug
    {
        return Bug::create([
            ...$data,
            'project_id' => $project->id,
            'reported_by' => $reporter->id,
            'status' => 'open',
        ]);
    }

    public function transition(Bug $bug, string $targetStatus, ?string $note = null): Bug
    {
        $allowed = self::TRANSITIONS[$bug->status] ?? [];

        if (! in_array($targetStatus, $allowed, true)) {
            throw ValidationException::withMessages([
                'status' => "Cannot move bug from '{$bug->status}' to '{$targetStatus}'.",
            ]);
        }

        $bug->update(['status' => $targetStatus]);

        // Notification hook (Sprint 6):
        // if ($targetStatus === 'fixed') notify QA pool on the project;
        // if ($targetStatus === 'open' && $note) notify assignee with reopen reason.

        return $bug;
    }

    /** Scoped visibility, same pattern as TaskService::visibleFor */
    public function visibleFor(Project $project, User $user)
    {
        $query = $project->bugs();

        $isManagerOrAbove = $user->can('permission', 'projects.view_any') || $project->isManagedBy($user);

        // QA sees all bugs on the project (they need the full picture to triage).
        // Developer sees only bugs assigned to them.
        // Everyone else covered above (client, etc.) sees all — read-only project visibility.
        $isQa = $project->staffWithRole('qa')->where('user_id', $user->id)->exists();

        if (! $isManagerOrAbove && ! $isQa) {
            $isDeveloper = $project->staffWithRole('developer')->where('user_id', $user->id)->exists();
            if ($isDeveloper) {
                $query->where('assigned_to', $user->id);
            }
        }

        return $query;
    }
}