<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Country;
use App\Models\District;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        // Create Yemen
        $yemen = Country::firstOrCreate(
            ['code' => 'YE'],
            [
                'name' => 'Yemen',
                'phone_code' => '967',
                'status' => true,
            ]
        );

        $cities = [
            'Sana\'a' => [
                'Al Sabeen',
                'Ma\'ain',
                'Al Wehda',
                'Old City',
                'Shu\'aub',
                'Az\'zal',
                'Al Safya',
                'At Tahrir',
                'Bani Al Harith',
                'Ath\'thaorah'
            ],
            'Aden' => [
                'Crater',
                'Khormaksar',
                'Al Mualla',
                'Tawahi',
                'Sheikh Othman',
                'Al Mansoura',
                'Dar Saad',
                'Al Buraiqeh'
            ],
            'Taiz' => [
                'Al Qahirah',
                'Al Mudhaffar',
                'Salh',
                'Sabir Al Mawadim',
                'Al Ta\'iziyah'
            ],
            'Ibb' => [
                'Al Dhihar',
                'Al Mashannah',
                'Jibla',
                'Ba\'dan'
            ],
            'Hadhramaut' => [
                'Mukalla',
                'Sayun',
                'Tarim',
                'Ash Shihr'
            ],
        ];

        foreach ($cities as $cityName => $districts) {
            $city = City::firstOrCreate(
                ['country_id' => $yemen->id, 'name' => $cityName],
                ['status' => true]
            );

            foreach ($districts as $districtName) {
                District::firstOrCreate(
                    ['city_id' => $city->id, 'name' => $districtName],
                    ['status' => true]
                );
            }
        }
    }
}
