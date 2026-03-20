<?php

namespace App\Http\Requests\Admin;

use App\Models\DiscountCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDiscountCodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $type = (string) $this->input('type');
        $amount = (string) $this->input('amount');

        if ($type === DiscountCode::TYPE_FIXED) {
            $normalized = number_format((float) $amount, 2, '.', '');
            $amountValue = (int) round(((float) $normalized) * 100);
        } else {
            $amountValue = (int) floor((float) $amount);
        }

        $this->merge([
            'code' => strtoupper(trim((string) $this->input('code'))),
            'amount' => $amountValue,
        ]);
    }

    public function rules(): array
    {
        /** @var DiscountCode $discountCode */
        $discountCode = $this->route('discountCode');

        return [
            'code' => ['required', 'string', 'max:64', Rule::unique('discount_codes', 'code')->ignore($discountCode->id)],
            'description' => ['nullable', 'string', 'max:255'],
            'type' => ['required', Rule::in(DiscountCode::types())],
            'amount' => ['required', 'integer', 'min:1'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'expires_at' => ['nullable', 'date'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
