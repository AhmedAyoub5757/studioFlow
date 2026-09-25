<?php

namespace App\Notifications;

use App\Models\Approval;
use App\Models\Milestone;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;


class MilestoneDecided extends Notification implements ShouldQueue
{
    use Queueable;

    protected Milestone $milestone;
    protected Approval $approval;

    public function __construct(Milestone $milestone, Approval $approval)
    {
        $this->milestone = $milestone;
        $this->approval = $approval;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $verb = $this->approval->status === 'approved' ? 'approved' : 'rejected';

        return [
            'title' => "Milestone {$verb}",
            'message' => "\"{$this->milestone->title}\" was {$verb} by the client."
                . ($this->approval->decision_note ? " Note: {$this->approval->decision_note}" : ''),
            'milestone_id' => $this->milestone->id,
            'project_id' => $this->milestone->project_id,
            'status' => $this->approval->status,
            'action_url' => "/projects/{$this->milestone->project_id}/milestones/{$this->milestone->id}",
        ];
    }
}