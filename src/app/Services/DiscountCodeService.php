<?php

namespace App\Services;

use App\Models\DiscountCode;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DiscountCodeService
{
    private const SESSION_KEY = 'cart.discount_code';

    public function current(): ?DiscountCode
    {
        $code = strtoupper(trim((string) session()->get(self::SESSION_KEY, '')));

        if ($code === '') {
            return null;
        }

        $discountCode = DiscountCode::query()
            ->where('code', $code)
            ->first();

        if (! $discountCode || ! $discountCode->isCurrentlyValid()) {
            $this->clear();

            return null;
        }

        return $discountCode;
    }

    /**
     * @return array{code: string, description: string|null, discount_cents: int, subtotal_cents: int, total_cents: int}|null
     */
    public function summary(int $subtotalCents): ?array
    {
        $discountCode = $this->current();

        if (! $discountCode) {
            return null;
        }

        $discountCents = $discountCode->discountCentsFor($subtotalCents);

        return [
            'code' => $discountCode->code,
            'description' => $discountCode->description,
            'discount_cents' => $discountCents,
            'subtotal_cents' => $subtotalCents,
            'total_cents' => max(0, $subtotalCents - $discountCents),
        ];
    }

    /**
     * @throws ValidationException
     */
    public function apply(string $code, int $subtotalCents): DiscountCode
    {
        $normalizedCode = strtoupper(trim($code));

        $discountCode = DiscountCode::query()
            ->where('code', $normalizedCode)
            ->first();

        if (! $discountCode || ! $discountCode->isCurrentlyValid()) {
            throw ValidationException::withMessages([
                'code' => 'That discount code is not available.',
            ]);
        }

        if ($discountCode->discountCentsFor($subtotalCents) <= 0) {
            throw ValidationException::withMessages([
                'code' => 'That discount code cannot be applied to this cart.',
            ]);
        }

        session()->put(self::SESSION_KEY, $discountCode->code);

        return $discountCode;
    }

    public function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    /**
     * @throws ValidationException
     */
    public function reserveCurrent(int $subtotalCents): ?DiscountCode
    {
        $current = $this->current();

        if (! $current) {
            return null;
        }

        /** @var DiscountCode|null $discountCode */
        $discountCode = DiscountCode::query()
            ->lockForUpdate()
            ->find($current->id);

        if (! $discountCode || ! $discountCode->isCurrentlyValid()) {
            $this->clear();

            throw ValidationException::withMessages([
                'cart' => 'The selected discount code is no longer available.',
            ]);
        }

        if ($discountCode->discountCentsFor($subtotalCents) <= 0) {
            $this->clear();

            throw ValidationException::withMessages([
                'cart' => 'The selected discount code can no longer be applied.',
            ]);
        }

        $discountCode->increment('times_used');

        return $discountCode->fresh();
    }

    public function release(?DiscountCode $discountCode): void
    {
        if (! $discountCode || $discountCode->times_used <= 0) {
            return;
        }

        DB::transaction(function () use ($discountCode): void {
            $freshCode = DiscountCode::query()
                ->lockForUpdate()
                ->find($discountCode->id);

            if (! $freshCode || $freshCode->times_used <= 0) {
                return;
            }

            $freshCode->decrement('times_used');
        });
    }
}
