<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $commentable = $this->route('task') ?? $this->route('milestone');

        return $this->user()->can('create', [\App\Models\Comment::class, $commentable]);
    }

    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'max:2000'],
        ];
    }
}
