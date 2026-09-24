<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMilestoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('milestone'));
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'due_date' => ['nullable', 'date'],
            'status' => ['sometimes', 'in:pending,in_progress,completed,approved'],
            'order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
