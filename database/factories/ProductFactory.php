<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Subcategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Product> */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'subcategory_id' => Subcategory::factory(),
            'category_id' => fn (array $data) => Subcategory::findOrFail($data['subcategory_id'])->category_id,
            'name' => fake()->words(3, true),
            'slug' => fn (array $data) => Str::slug($data['name']).'-'.Str::lower(Str::random(8)),
            'description' => fake()->paragraph(),
            'image' => 'demo/headphones.webp',
            'old_price' => '129.00',
            'new_price' => '99.00',
            'is_active' => true,
        ];
    }
}
