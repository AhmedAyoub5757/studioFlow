<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;


class TaskAssigned extends Notification implements ShouldQueue
{
    use Queueable;

    protected Task $task;

    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'title' => 'New task assigned to you',
            'message' => "You've been assigned: \"{$this->task->title}\"",
            'task_id' => $this->task->id,
            'project_id' => $this->task->project_id,
            'action_url' => "/projects/{$this->task->project_id}/tasks/{$this->task->id}",
        ];
    }
}