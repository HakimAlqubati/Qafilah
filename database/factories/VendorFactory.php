<?php
// database/factories/VendorFactory.php

namespace Database\Factories;

use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class VendorFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Vendor::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Generate a unique name and slug
        $name = $this->faker->unique()->company();

        // Ensure a user exists to set as creator/editor
        $user_id = User::inRandomOrder()->first()->id ?? User::factory()->create()->id;

        return [
            'name'             => $name,
            'slug'             => Str::slug($name),
            'contact_person'   => $this->faker->name(),
            'email'            => $this->faker->unique()->companyEmail(),
            'phone'            => $this->faker->phoneNumber(),

            // Generate a unique VAT ID (using a specific format for realism)
            'vat_id'           => $this->faker->unique()->numerify('SA##########'),

            'status'           => $this->faker->randomElement(['active', 'inactive', 'pending']),
            'description'      => $this->faker->text(200),

            // Note: logo_path is typically handled by Media Library, but here we provide a dummy path
            'logo_path'        => 'vendors/logos/' . $this->faker->slug() . '.png',

            // Audit fields: Ensure FK integrity
            'created_by'       => $user_id,
            'updated_by'       => $user_id,

            'created_at'       => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at'       => $this->faker->dateTimeBetween('-6 months', 'now'),
        ];
    }

    /**
     * Indicate that the vendor is active.
     */
    public function active(): Factory
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'active',
        ]);
    }

    /**
     * Indicate that the vendor is inactive.
     */
    public function inactive(): Factory
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'inactive',
        ]);
    }
}
