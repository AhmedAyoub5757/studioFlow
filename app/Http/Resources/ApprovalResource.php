<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApprovalResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'requested_by' => $this->whenLoaded('requester', function () {
                return $this->requester ? $this->requester->name : null;
            }),
            'decided_by' => $this->whenLoaded('decider', function () {
                return $this->decider ? $this->decider->name : null;
            }),
            'decision_note' => $this->decision_note,
            'decided_at' => $this->decided_at,
            'created_at' => $this->created_at,
        ];
    }
}