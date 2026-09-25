<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'name' => $this->name,
            'amount' => $this->amount,
            'billing_cycle' => $this->billing_cycle,
            'status' => $this->status,
            'next_billing_date' => $this->next_billing_date,
            'started_at' => $this->started_at,
        ];
    }
}
