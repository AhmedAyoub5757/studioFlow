<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'method' => $this->method,
            'status' => $this->status,
            'transaction_ref' => $this->transaction_ref,
            'paid_by' => $this->whenLoaded('payer', fn () => $this->payer->name),
            'created_at' => $this->created_at,
        ];
    }
}
