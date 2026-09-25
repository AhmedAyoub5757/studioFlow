<?php

namespace App\Notifications;

use App\Models\Bug;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;


class BugStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    protected Bug $bug;
    protected string $previousStatus;

    public function __construct(Bug $bug, string $previousStatus)
    {
        $this->bug = $bug;
        $this->previousStatus = $previousStatus;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'title' => 'Bug status changed',
            'message' => "\"{$this->bug->title}\" moved from {$this->previousStatus} to {$this->bug->status}.",
            'bug_id' => $this->bug->id,
            'project_id' => $this->bug->project_id,
            'action_url' => "/projects/{$this->bug->project_id}/bugs/{$this->bug->id}",
        ];
    }
}