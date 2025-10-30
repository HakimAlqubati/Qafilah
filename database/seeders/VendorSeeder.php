<?php

// database/seeders/VendorSeeder.php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Seeder;

class VendorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 🚨 Prerequisite Check: Ensure at least one User exists for the 'created_by' foreign key
        if (User::count() === 0) {
            // Optional: Create a default admin user if none exists
            User::factory()->create([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
            ]);
        }

        // 1. Create a large batch of vendors (e.g., 50)
        Vendor::factory()
            ->count(40)
            ->create();

        // 2. Create some special/specific vendors for easy testing
        Vendor::factory()
            ->active()
            ->count(5)
            ->create();

        Vendor::factory()
            ->inactive()
            ->count(5)
            ->create();
    }
}
