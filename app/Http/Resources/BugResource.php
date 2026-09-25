<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BugResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'task_id' => $this->task_id,
            'title' => $this->title,
            'description' => $this->description,
            'steps_to_reproduce' => $this->steps_to_reproduce,
            'severity' => $this->severity,
            'status' => $this->status,
            'reporter' => $this->whenLoaded('reporter', fn () => $this->reporter->name),
            'assignee' => $this->whenLoaded('assignee', fn () => $this->assignee ? [
                'id' => $this->assignee->id,
                'name' => $this->assignee->name,
            ] : null),
            'created_at' => $this->created_at,
        ];
    }
}
