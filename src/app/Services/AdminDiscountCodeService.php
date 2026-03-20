<?php

namespace App\Services;

use App\Models\DiscountCode;

class AdminDiscountCodeService
{
    public function create(array $data): DiscountCode
    {
        return DiscountCode::query()->create([
            ...$data,
            'is_active' => (bool) ($data['is_active'] ?? false),
        ]);
    }

    public function update(DiscountCode $discountCode, array $data): DiscountCode
    {
        $discountCode->update([
            ...$data,
            'is_active' => (bool) ($data['is_active'] ?? false),
        ]);

        return $discountCode->fresh();
    }

    public function archive(DiscountCode $discountCode): DiscountCode
    {
        $discountCode->update([
            'is_active' => false,
        ]);

        return $discountCode->fresh();
    }

    public function restore(DiscountCode $discountCode): DiscountCode
    {
        $discountCode->update([
            'is_active' => true,
        ]);

        return $discountCode->fresh();
    }
}
