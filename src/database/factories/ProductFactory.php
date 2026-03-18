<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = Str::title(fake()->words(3, true));

        return [
            'category_id' => Category::factory(),
            'name' => $name,
            'slug' => Str::slug($name.'-'.fake()->unique()->numberBetween(100, 999)),
            'sku' => 'VF-'.strtoupper(fake()->bothify('??###')),
            'description' => fake()->paragraphs(2, true),
            'price_cents' => fake()->numberBetween(2400, 4200),
            'stock' => fake()->numberBetween(0, 40),
            'is_active' => true,
        ];
    }
}
