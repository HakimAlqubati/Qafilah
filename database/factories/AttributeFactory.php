<?php

namespace Database\Factories;

use App\Models\Attribute;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttributeFactory extends Factory
{
    protected $model = Attribute::class;

    public function definition(): array
    {
        $inputTypes = ['text', 'number', 'select', 'radio', 'boolean', 'date'];

        return [
            'code' => $this->faker->unique()->slug(2),
            'name' => ucfirst($this->faker->word()),
            'input_type' => $this->faker->randomElement($inputTypes),
            'is_required' => $this->faker->boolean(30),
            'active' => true,
        ];
    }
}
