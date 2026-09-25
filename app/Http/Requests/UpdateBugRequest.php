<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBugRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('bug'));
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string'],
            'steps_to_reproduce' => ['nullable', 'string'],
            'severity' => ['sometimes', 'in:low,medium,high,critical'],
            'assigned_to' => ['nullable', 'exists:users,id'],
        ];
    }
}