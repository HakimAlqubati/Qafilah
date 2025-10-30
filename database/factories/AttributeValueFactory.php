<?php

namespace Database\Factories;

use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttributeValueFactory extends Factory
{
    protected $model = AttributeValue::class;

    public function definition(): array
    {
        // نحصل على أي خاصية موجودة (إن وجدت)
        $attribute = Attribute::inRandomOrder()->first();

        return [
            'attribute_id' => $attribute?->id ?? Attribute::factory(),
            'value' => $this->faker->unique()->word(),
            'sort_order' => $this->faker->numberBetween(1, 10),
        ];
    }
}
