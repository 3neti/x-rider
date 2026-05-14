<?php

namespace LBHurtado\XRider\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResolveRiderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'state' => ['nullable', 'string'],
            'rider' => ['nullable', 'array'],
            'campaign' => ['nullable', 'array'],
            'ads' => ['nullable', 'array'],
            'analytics' => ['nullable', 'array'],
            'meta' => ['nullable', 'array'],
        ];
    }
}
