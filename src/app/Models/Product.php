<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory, SoftDeletes;

    public const DEFAULT_SHIRT_SIZE = 'M';

    public const SHIRT_SIZES = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'sku',
        'description',
        'image_path',
        'back_image_path',
        'disabled_sizes',
        'price_cents',
        'stock',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'disabled_sizes' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Scope a query to only include active products.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to products that are visible in the public storefront.
     */
    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query
            ->active()
            ->whereHas('category', fn (Builder $categoryQuery) => $categoryQuery->whereNull('deleted_at'));
    }

    /**
     * Get the category that owns the product.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the front image URL for the product.
     */
    public function getImageUrlAttribute(): string
    {
        return $this->front_image_url;
    }

    /**
     * Get the front image URL for the product.
     */
    public function getFrontImageUrlAttribute(): string
    {
        if ($this->image_path && Storage::disk('public')->exists($this->image_path)) {
            return route('media.show', ['path' => $this->image_path]);
        }

        $relativePath = 'images/items/'.$this->slug.'.svg';

        if (file_exists(public_path($relativePath))) {
            return asset($relativePath);
        }

        return asset('images/items/fallback.svg');
    }

    /**
     * Get the back image URL for the product.
     */
    public function getBackImageUrlAttribute(): string
    {
        if ($this->back_image_path && Storage::disk('public')->exists($this->back_image_path)) {
            return route('media.show', ['path' => $this->back_image_path]);
        }

        return $this->front_image_url;
    }

    /**
     * Get a storefront-friendly stock label without exposing exact counts.
     */
    public function getStockLabelAttribute(): string
    {
        if ($this->stock <= 0) {
            return 'No stock';
        }

        if ($this->stock <= 5) {
            return 'Limited stock';
        }

        return 'In stock';
    }

    /**
     * Get the selectable shirt sizes for the storefront.
     *
     * @return list<string>
     */
    public static function shirtSizes(): array
    {
        return self::SHIRT_SIZES;
    }

    /**
     * Get the disabled sizes for this shirt.
     *
     * @return list<string>
     */
    public function disabledShirtSizes(): array
    {
        return collect($this->disabled_sizes ?? [])
            ->map(fn (mixed $size) => strtoupper((string) $size))
            ->filter(fn (string $size) => in_array($size, self::shirtSizes(), true))
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Get the sizes currently available for this shirt.
     *
     * @return list<string>
     */
    public function availableShirtSizes(): array
    {
        return array_values(array_diff(self::shirtSizes(), $this->disabledShirtSizes()));
    }

    /**
     * Determine whether a given shirt size can currently be purchased.
     */
    public function isShirtSizeAvailable(string $size): bool
    {
        return in_array(strtoupper($size), $this->availableShirtSizes(), true);
    }

    /**
     * Get the default purchasable size for storefront quick add actions.
     */
    public function defaultShirtSize(): ?string
    {
        if ($this->isShirtSizeAvailable(self::DEFAULT_SHIRT_SIZE)) {
            return self::DEFAULT_SHIRT_SIZE;
        }

        return $this->availableShirtSizes()[0] ?? null;
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
