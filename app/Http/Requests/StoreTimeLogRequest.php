<?php

namespace App\Http\Requests;

use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;

class StoreTimeLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        $task = Task::findOrFail($this->route('task'));
        return $this->user()->can('create', [\App\Models\TimeLog::class, $task]);
    }

    public function rules(): array
    {
        return [
            'hours' => ['required', 'numeric', 'min:0.25', 'max:24'],
            'log_date' => ['required', 'date', 'before_or_equal:today'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }
}

