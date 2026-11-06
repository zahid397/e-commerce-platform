<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Subcategory> */
class SubcategoryFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return ['category_id' => Category::factory(), 'name' => $name, 'slug' => Str::slug($name)];
    }
}
