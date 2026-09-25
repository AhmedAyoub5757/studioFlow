<?php

namespace App\Notifications;

use App\Models\Milestone;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class MilestoneReadyForApproval extends Notification implements ShouldQueue
{
    use Queueable;

    protected Milestone $milestone;

    public function __construct(Milestone $milestone)
    {
        $this->milestone = $milestone;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'title' => 'Milestone ready for your approval',
            'message' => "\"{$this->milestone->title}\" is complete and awaiting your review.",
            'milestone_id' => $this->milestone->id,
            'project_id' => $this->milestone->project_id,
            'action_url' => "/projects/{$this->milestone->project_id}/milestones/{$this->milestone->id}",
        ];
    }
}