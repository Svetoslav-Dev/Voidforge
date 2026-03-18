<?php

namespace App\Http\Requests\Admin;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
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
        /** @var Product $product */
        $product = $this->route('product');

        return [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('products', 'slug')->ignore($product->id)],
            'sku' => ['required', 'string', 'max:255', Rule::unique('products', 'sku')->ignore($product->id)],
            'description' => ['required', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'back_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'stock' => ['required', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'disabled_sizes' => ['nullable', 'array'],
            'disabled_sizes.*' => ['string', Rule::in(Product::shirtSizes())],
        ];
    }

    /**
     * Normalize request data before validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'price' => $this->normalizedPrice($this->input('price')),
        ]);
    }

    private function normalizedPrice(mixed $price): mixed
    {
        if (! is_string($price) && ! is_numeric($price)) {
            return $price;
        }

        $priceString = str_replace(',', '.', trim((string) $price));

        if (! preg_match('/^\d+(?:\.\d+)?$/', $priceString)) {
            return $price;
        }

        [$wholePart, $decimalPart] = array_pad(explode('.', $priceString, 2), 2, '');
        $decimalPart = str_pad(substr($decimalPart, 0, 2), 2, '0');

        return $wholePart.'.'.$decimalPart;
    }
}
