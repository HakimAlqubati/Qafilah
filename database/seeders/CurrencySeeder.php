<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currencies = [
            [
                'name' => 'US Dollar',
                'code' => 'USD',
                'symbol' => '$',
                'rate' => 1.0000,
                'is_default' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Saudi Riyal',
                'code' => 'SAR',
                'symbol' => 'SAR',
                'rate' => 3.7500,
                'is_default' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Yemeni Rial',
                'code' => 'YER',
                'symbol' => 'YER',
                'rate' => 250.0000,
                'is_default' => false,
                'is_active' => true,
            ],
        ];

        foreach ($currencies as $currency) {
            Currency::updateOrCreate(['code' => $currency['code']], $currency);
        }
    }
}
