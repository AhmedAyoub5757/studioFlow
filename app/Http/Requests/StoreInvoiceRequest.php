<?php

namespace App\Http\Requests;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $project = Project::findOrFail($this->route('project'));
        return $this->user()->can('create', [\App\Models\Invoice::class, $project]);
    }

    public function rules(): array
    {
        return [
            'milestone_id' => ['nullable', 'exists:milestones,id'],
            'type' => ['required', 'in:deposit,milestone,final,subscription'],
            'tax' => ['nullable', 'numeric', 'min:0'],
            'due_date' => ['nullable', 'date', 'after_or_equal:today'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->milestone_id) {
                $project = Project::findOrFail($this->route('project'));
                $milestone = \App\Models\Milestone::find($this->milestone_id);
                if ($milestone && $milestone->project_id !== $project->id) {
                    $validator->errors()->add('milestone_id', 'Milestone does not belong to this project.');
                }
            }
        });
    }
}
