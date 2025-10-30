<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\AttributeSet;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'name' => ucfirst($this->faker->unique()->word()),
            'parent_id' => null,
            'attribute_set_id' => AttributeSet::inRandomOrder()->first()?->id,
            'active' => true,
        ];
    }
}
