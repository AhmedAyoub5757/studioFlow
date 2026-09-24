<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'milestone_id' => $this->milestone_id,
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type,
            'status' => $this->status,
            'priority' => $this->priority,
            'due_date' => $this->due_date,
            'assignee' => $this->whenLoaded('assignee', fn () => $this->assignee ? [
                'id' => $this->assignee->id,
                'name' => $this->assignee->name,
            ] : null),
            'created_by' => $this->whenLoaded('creator', fn () => $this->creator->name),
            'logged_hours' => $this->whenLoaded('timeLogs', fn () => $this->timeLogs->sum('hours')),
            'created_at' => $this->created_at,
        ];
    }
}
