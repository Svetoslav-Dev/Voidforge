<?php

namespace App\Http\Requests\Account;

use App\Http\Requests\Checkout\StoreCheckoutRequest;
use Illuminate\Foundation\Http\FormRequest;

class StoreShippingAddressRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'label' => ['required', 'string', 'max:255'],
            'recipient_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address_line_1' => ['required', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['required', 'string', 'max:50'],
            'country' => ['required', 'string', 'size:2', 'in:'.implode(',', array_keys(StoreCheckoutRequest::europeanCountries()))],
            'is_default' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Normalize request data before validation.
     */
    protected function prepareForValidation(): void
    {
        $country = $this->input('country');

        $this->merge([
            'country' => is_string($country) ? strtoupper(trim($country)) : $country,
        ]);
    }
}
