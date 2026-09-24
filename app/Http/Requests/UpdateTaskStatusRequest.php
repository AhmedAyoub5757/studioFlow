<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('updateStatus', $this->route('task'));
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'in:todo,in_progress,in_review,done'],
        ];
    }
}
