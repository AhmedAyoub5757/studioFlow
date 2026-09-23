<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'status' => $this->status,
            'budget' => $this->budget,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'client' => [
                'id' => $this->client->id,
                'name' => $this->client->name,
            ],
            'created_by' => $this->creator->name,
            'staff' => $this->whenLoaded('staff', fn () => $this->staff->map(fn ($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'role_on_project' => $u->pivot->role_on_project,
            ])),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}