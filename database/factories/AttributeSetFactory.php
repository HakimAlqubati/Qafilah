<?php

namespace Database\Factories;

use App\Models\AttributeSet;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttributeSetFactory extends Factory
{
    protected $model = AttributeSet::class;

    public function definition(): array
    {
        return [
            'name' => ucfirst($this->faker->unique()->word()) . ' Attributes',
            'description' => $this->faker->sentence(),
            'active' => true,
        ];
    }
}
