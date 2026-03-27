<?php

namespace App\Http\Requests\Legal;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLegalContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'topic' => ['required', Rule::in(['privacy', 'returns', 'order_support', 'general'])],
            'order_reference' => ['nullable', 'string', 'max:40'],
            'message' => ['required', 'string', 'max:4000'],
        ];
    }
}
