<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class DiscountCode extends Model
{
    use HasFactory;

    public const TYPE_FIXED = 'fixed';
    public const TYPE_PERCENT = 'percent';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'description',
        'type',
        'amount',
        'is_active',
        'usage_limit',
        'times_used',
        'expires_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'expires_at' => 'datetime',
        ];
    }

    public static function types(): array
    {
        return [
            self::TYPE_FIXED,
            self::TYPE_PERCENT,
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function isCurrentlyValid(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->expires_at instanceof Carbon && $this->expires_at->isPast()) {
            return false;
        }

        if ($this->usage_limit !== null && $this->times_used >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    public function discountCentsFor(int $subtotalCents): int
    {
        if ($subtotalCents <= 0) {
            return 0;
        }

        $discountCents = $this->type === self::TYPE_PERCENT
            ? (int) floor($subtotalCents * ($this->amount / 100))
            : (int) $this->amount;

        return max(0, min($subtotalCents, $discountCents));
    }
}
