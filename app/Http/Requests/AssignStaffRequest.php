<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignStaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('assignStaff', $this->route('project'));
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'exists:users,id'],
            'role_on_project' => ['required', 'in:manager,developer,designer,qa'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $user = \App\Models\User::find($this->user_id);
            if (! $user) return;

            // The system role must match the project role being assigned —
            // you can't assign someone with only a Developer system role as 'manager'.
            $map = [
                'manager' => ['project_manager', 'agency_manager'],
                'developer' => ['developer'],
                'designer' => ['designer'],
                'qa' => ['qa'],
            ];

            $allowedSystemRoles = $map[$this->role_on_project] ?? [];
            $userRoleNames = $user->roles->pluck('name')->toArray();

            if (empty(array_intersect($allowedSystemRoles, $userRoleNames))) {
                $validator->errors()->add(
                    'user_id',
                    "User's system role doesn't match '{$this->role_on_project}'."
                );
            }
        });
    }
}