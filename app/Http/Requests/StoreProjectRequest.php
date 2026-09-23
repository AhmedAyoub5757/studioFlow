<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Project::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'client_id' => ['required', 'exists:users,id'],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $client = \App\Models\User::find($this->client_id);
            if ($client && ! $client->hasRole('client')) {
                $validator->errors()->add('client_id', 'Selected user is not a Client.');
            }
        });
    }
}