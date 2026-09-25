<?php

namespace App\Services;

use App\Models\Approval;
use App\Models\Milestone;
use App\Models\User;
use App\Notifications\MilestoneReadyForApproval;
use App\Notifications\MilestoneDecided;
use Illuminate\Validation\ValidationException;

class ApprovalService
{
    public function request(Milestone $milestone, User $requestedBy): Approval
    {
        if ($milestone->pendingApproval) {
            throw ValidationException::withMessages([
                'milestone' => 'An approval is already pending for this milestone.',
            ]);
        }

        $approval = $milestone->approvals()->create([
            'requested_by' => $requestedBy->id,
            'status' => 'pending',
        ]);

        $milestone->update(['status' => 'completed']); // "done, awaiting client sign-off"

        $milestone->project->client->notify(new MilestoneReadyForApproval($milestone));

        return $approval;
    }

    public function decide(Milestone $milestone, User $decidedBy, string $status, ?string $note): Approval
    {
        $approval = $milestone->pendingApproval;

        if (! $approval) {
            throw ValidationException::withMessages([
                'milestone' => 'No pending approval to decide on for this milestone.',
            ]);
        }

        $approval->update([
            'status' => $status,
            'decided_by' => $decidedBy->id,
            'decision_note' => $note,
            'decided_at' => now(),
        ]);

        $milestone->update([
            'status' => $status === 'approved' ? 'approved' : 'in_progress', // rejected -> back to work
        ]);

        foreach ($milestone->project->staffWithRole('manager')->get() as $manager) {
            $manager->notify(new MilestoneDecided($milestone, $approval));
        }

        return $approval;
    }
}