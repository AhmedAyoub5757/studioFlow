<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RequestApprovalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('request', $this->route('milestone'));
    }

    public function rules(): array
    {
        return []; // no body needed — the act of calling this endpoint IS the request
    }
}
