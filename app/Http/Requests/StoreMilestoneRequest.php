<?php

namespace App\Http\Requests;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;

class StoreMilestoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        $project = Project::findOrFail($this->route('project'));
        return $this->user()->can('create', [\App\Models\Milestone::class, $project]);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'due_date' => ['nullable', 'date'],
            'order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}