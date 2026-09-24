<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MilestoneResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'title' => $this->title,
            'description' => $this->description,
            'amount' => $this->amount,
            'due_date' => $this->due_date,
            'status' => $this->status,
            'order' => $this->order,
            'tasks_count' => $this->whenCounted('tasks'),
            'created_at' => $this->created_at,
        ];
    }
}