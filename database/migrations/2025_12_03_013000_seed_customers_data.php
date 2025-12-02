<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\City;
use App\Models\District;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ensure we have some cities/districts or handle nulls
        $cityId = City::first()?->id;
        $districtId = District::first()?->id;

        $customers = [
            [
                'name' => 'Al-Rajhi Markets',
                'contact_person' => 'Ahmed Al-Rajhi',
                'email' => 'contact@alrajhi-markets.com',
                'phone' => '0501234567',
                'vat_number' => '300123456700003',
                'commercial_register' => '1010123456',
                'credit_limit' => 50000,
                'payment_terms' => 'Net 30',
            ],
            [
                'name' => 'Panda Retail Company',
                'contact_person' => 'Khalid Al-Otaibi',
                'email' => 'purchasing@panda.com.sa',
                'phone' => '0509876543',
                'vat_number' => '300987654300003',
                'commercial_register' => '1010987654',
                'credit_limit' => 100000,
                'payment_terms' => 'Net 60',
            ],
            [
                'name' => 'Othaim Markets',
                'contact_person' => 'Fahad Al-Othaim',
                'email' => 'info@othaimmarkets.com',
                'phone' => '0551112222',
                'vat_number' => '310123456700003',
                'commercial_register' => '1010111222',
                'credit_limit' => 75000,
                'payment_terms' => 'Net 45',
            ],
            [
                'name' => 'Tamimi Markets',
                'contact_person' => 'Sarah Al-Tamimi',
                'email' => 'procurement@tamimimarkets.com',
                'phone' => '0543334444',
                'vat_number' => '320123456700003',
                'commercial_register' => '1010333444',
                'credit_limit' => 60000,
                'payment_terms' => 'Net 30',
            ],
            [
                'name' => 'Danube Hypermarket',
                'contact_person' => 'Mohammed Bin Dawood',
                'email' => 'sales@danube.sa',
                'phone' => '0565556666',
                'vat_number' => '330123456700003',
                'commercial_register' => '1010555666',
                'credit_limit' => 80000,
                'payment_terms' => 'Net 45',
            ],
            [
                'name' => 'Lulu Hypermarket',
                'contact_person' => 'Yusuff Ali',
                'email' => 'ksa@lulugroup.com',
                'phone' => '0537778888',
                'vat_number' => '340123456700003',
                'commercial_register' => '1010777888',
                'credit_limit' => 120000,
                'payment_terms' => 'Net 60',
            ],
            [
                'name' => 'Carrefour KSA',
                'contact_person' => 'Majid Al Futtaim',
                'email' => 'b2b@carrefourksa.com',
                'phone' => '0509990000',
                'vat_number' => '350123456700003',
                'commercial_register' => '1010999000',
                'credit_limit' => 90000,
                'payment_terms' => 'Net 45',
            ],
            [
                'name' => 'Farm Superstores',
                'contact_person' => 'Hazem Al-Aswad',
                'email' => 'info@farm.com.sa',
                'phone' => '0551231234',
                'vat_number' => '360123456700003',
                'commercial_register' => '1010123123',
                'credit_limit' => 40000,
                'payment_terms' => 'Net 30',
            ],
            [
                'name' => 'Bin Dawood',
                'contact_person' => 'Abdulrazzaq Bin Dawood',
                'email' => 'contact@bindawood.com',
                'phone' => '0544564567',
                'vat_number' => '370123456700003',
                'commercial_register' => '1010456456',
                'credit_limit' => 55000,
                'payment_terms' => 'Net 30',
            ],
            [
                'name' => 'Al Sadhan Markets',
                'contact_person' => 'Mazen Al Sadhan',
                'email' => 'supply@al-sadhan.com',
                'phone' => '0567897890',
                'vat_number' => '380123456700003',
                'commercial_register' => '1010789789',
                'credit_limit' => 35000,
                'payment_terms' => 'Cash',
            ],
            [
                'name' => 'Spar Saudi Arabia',
                'contact_person' => 'Omar Al-Khattab',
                'email' => 'info@spar.sa',
                'phone' => '0501113333',
                'vat_number' => '390123456700003',
                'commercial_register' => '1010111333',
                'credit_limit' => 30000,
                'payment_terms' => 'Net 15',
            ],
            [
                'name' => 'Manuel Market',
                'contact_person' => 'Khalid Darwish',
                'email' => 'imports@manuel.com.sa',
                'phone' => '0552224444',
                'vat_number' => '301123456700003',
                'commercial_register' => '1010222444',
                'credit_limit' => 45000,
                'payment_terms' => 'Net 30',
            ],
            [
                'name' => 'Sarawat Superstores',
                'contact_person' => 'Abdullah Sarawat',
                'email' => 'orders@sarawat.com',
                'phone' => '0543335555',
                'vat_number' => '302123456700003',
                'commercial_register' => '1010333555',
                'credit_limit' => 25000,
                'payment_terms' => 'Cash',
            ],
            [
                'name' => 'Nesto Hypermarket',
                'contact_person' => 'Basheer Nesto',
                'email' => 'riyadh@nestogroup.com',
                'phone' => '0564446666',
                'vat_number' => '303123456700003',
                'commercial_register' => '1010444666',
                'credit_limit' => 65000,
                'payment_terms' => 'Net 45',
            ],
            [
                'name' => 'Grand Mart',
                'contact_person' => 'Shamsudheen',
                'email' => 'info@grandmart.sa',
                'phone' => '0535557777',
                'vat_number' => '304123456700003',
                'commercial_register' => '1010555777',
                'credit_limit' => 20000,
                'payment_terms' => 'Cash',
            ],
        ];

        foreach ($customers as $data) {
            $customer = Customer::create($data);

            // Add Default Address
            CustomerAddress::create([
                'customer_id' => $customer->id,
                'type' => 'general',
                'city_id' => $cityId,
                'district_id' => $districtId,
                'address' => 'King Fahd Road, Building ' . rand(100, 999),
                'is_default' => true,
            ]);

            // Add Shipping Address
            CustomerAddress::create([
                'customer_id' => $customer->id,
                'type' => 'shipping',
                'city_id' => $cityId,
                'district_id' => $districtId,
                'address' => 'Warehouse District, Street ' . rand(1, 50),
                'is_default' => false,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optional: Delete the seeded customers
        // Customer::where('created_at', '>=', now()->subMinute())->forceDelete();
    }
};
