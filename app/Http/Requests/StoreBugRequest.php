<?php

namespace App\Http\Requests;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;

class StoreBugRequest extends FormRequest
{
    public function authorize(): bool
    {
        $project = Project::findOrFail($this->route('project'));
        return $this->user()->can('create', [\App\Models\Bug::class, $project]);
    }

    public function rules(): array
    {
        return [
            'task_id' => ['nullable', 'exists:tasks,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'steps_to_reproduce' => ['nullable', 'string'],
            'severity' => ['required', 'in:low,medium,high,critical'],
            'assigned_to' => ['nullable', 'exists:users,id'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $project = Project::findOrFail($this->route('project'));

            if ($this->task_id) {
                $task = \App\Models\Task::find($this->task_id);
                if ($task && $task->project_id !== $project->id) {
                    $validator->errors()->add('task_id', 'Task does not belong to this project.');
                }
            }

            if ($this->assigned_to && ! $project->staff()->where('user_id', $this->assigned_to)->exists()) {
                $validator->errors()->add('assigned_to', 'User is not assigned to this project.');
            }
        });
    }
}
