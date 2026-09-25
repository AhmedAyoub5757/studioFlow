<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DecideApprovalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('decide', $this->route('milestone'));
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'in:approved,rejected'],
            'decision_note' => ['nullable', 'string', 'max:1000', 'required_if:status,rejected'],
        ];
    }
}