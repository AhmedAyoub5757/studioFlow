<?php

namespace App\Http\Requests;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        $project = Project::findOrFail($this->route('project'));
        return $this->user()->can('create', [\App\Models\Task::class, $project]);
    }

    public function rules(): array
    {
        return [
            'milestone_id' => ['nullable', 'exists:milestones,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'type' => ['required', 'in:development,design,qa,general'],
            'priority' => ['nullable', 'in:low,medium,high,urgent'],
            'due_date' => ['nullable', 'date'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $project = Project::findOrFail($this->route('project'));

            // milestone must belong to the SAME project — prevents cross-project mixups
            if ($this->milestone_id) {
                $milestone = \App\Models\Milestone::find($this->milestone_id);
                if ($milestone && $milestone->project_id !== $project->id) {
                    $validator->errors()->add('milestone_id', 'Milestone does not belong to this project.');
                }
            }

            // assignee must actually be staff on this project
            if ($this->assigned_to && ! $project->staff()->where('user_id', $this->assigned_to)->exists()) {
                $validator->errors()->add('assigned_to', 'User is not assigned to this project.');
            }
        });
    }
}
