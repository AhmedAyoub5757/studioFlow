<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('task'));
    }

    public function rules(): array
    {
        return [
            'milestone_id' => ['nullable', 'exists:milestones,id'],
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'type' => ['sometimes', 'in:development,design,qa,general'],
            'priority' => ['sometimes', 'in:low,medium,high,urgent'],
            'due_date' => ['nullable', 'date'],
        ];
    }
}