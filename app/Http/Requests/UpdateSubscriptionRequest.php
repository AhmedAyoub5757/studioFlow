<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage', \App\Models\Subscription::class);
    }

    public function rules(): array
    {
        return [
            'status' => ['sometimes', 'in:active,paused,cancelled'],
            'amount' => ['sometimes', 'numeric', 'min:0'],
        ];
    }
}