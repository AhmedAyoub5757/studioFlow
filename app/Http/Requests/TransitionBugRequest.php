<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransitionBugRequest extends FormRequest
{
    public function authorize(): bool
    {
        $bug = $this->route('bug');
        $target = $this->input('status');

        switch ($target) {
            case 'in_progress':
            case 'fixed':
                return $this->user()->can('transitionAsDeveloper', $bug);

            case 'closed':
            case 'open':
                return $this->user()->can('transitionAsQa', $bug);

            case 'wont_fix':
                return $this->user()->can('markWontFix', $bug);

            default:
                return false;
        }
    }

    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['open', 'in_progress', 'fixed', 'closed', 'wont_fix'])],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }
}