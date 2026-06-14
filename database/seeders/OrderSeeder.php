<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Vendor;
use App\Models\Customer;
use App\Models\Currency;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('ar_SA');
        
        $vendors = Vendor::all();
        $customers = Customer::all();
        $currency = Currency::first();
        $products = Product::all();

        if ($vendors->isEmpty() || $customers->isEmpty() || !$currency || $products->isEmpty()) {
            $this->command->warn('Please ensure Vendors, Customers, Currencies, and Products exist before seeding orders.');
            return;
        }

        $orderStatuses = array_keys(Order::STATUSES);
        $paymentStatuses = array_keys(Order::PAYMENT_STATUSES);
        $shippingStatuses = array_keys(Order::SHIPPING_STATUSES);

        foreach ($vendors as $vendor) {
            // Add 10 to 30 orders per vendor
            $numOrders = rand(10, 30);
            $this->command->info("Creating {$numOrders} orders for Vendor: {$vendor->id} ({$vendor->store_name})");

            for ($i = 0; $i < $numOrders; $i++) {
                $customer = $customers->random();
                
                // Random past date between 1 year ago and today
                $createdAt = Carbon::now()->subDays(rand(1, 365))->subHours(rand(1, 24));
                $status = $orderStatuses[array_rand($orderStatuses)];
                
                $orderData = [
                    'order_number' => 'ORD-' . $createdAt->format('Ymd') . '-' . strtoupper(Str::random(6)),
                    'customer_id' => $customer->id,
                    'vendor_id' => $vendor->id,
                    'currency_id' => $currency->id,
                    'status' => $status,
                    'payment_status' => $paymentStatuses[array_rand($paymentStatuses)],
                    'shipping_status' => $shippingStatuses[array_rand($shippingStatuses)],
                    'shipping_address_id' => $customer->addresses()->inRandomOrder()->first()?->id,
                    'billing_address_id' => $customer->addresses()->inRandomOrder()->first()?->id,
                    'notes' => $faker->optional()->realText(50),
                    'internal_notes' => $faker->optional()->realText(50),
                    'placed_at' => $createdAt,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ];

                if ($status !== Order::STATUS_PENDING) {
                    $orderData['confirmed_at'] = (clone $createdAt)->addHours(rand(1, 12));
                }

                if (in_array($status, [Order::STATUS_SHIPPED, Order::STATUS_DELIVERED, Order::STATUS_COMPLETED])) {
                    $orderData['shipped_at'] = (clone $createdAt)->addDays(rand(1, 3));
                }

                if (in_array($status, [Order::STATUS_DELIVERED, Order::STATUS_COMPLETED])) {
                    $orderData['delivered_at'] = (clone $createdAt)->addDays(rand(4, 7));
                }

                // Temporary zero totals, we'll recalculate later
                $orderData['subtotal'] = 0;
                $orderData['tax_amount'] = 0;
                $orderData['discount_amount'] = 0;
                $orderData['shipping_amount'] = rand(10, 50);
                $orderData['total'] = 0;

                $order = Order::create($orderData);

                // Add 1 to 5 random items
                $numItems = rand(1, 5);
                for ($j = 0; $j < $numItems; $j++) {
                    $product = $products->random();
                    $quantity = rand(1, 5);
                    $unitPrice = rand(10, 500);
                    $tax = $unitPrice * 0.15 * $quantity;
                    $discount = rand(0, 10);
                    
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'sku' => 'SKU-' . strtoupper(Str::random(6)),
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'tax' => $tax,
                        'discount' => $discount,
                        'total' => ($quantity * $unitPrice) + $tax - $discount,
                    ]);
                }

                // Status history
                $order->statusHistory()->create([
                    'status' => Order::STATUS_PENDING,
                    'comment' => 'تم إنشاء الطلب',
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                if ($status !== Order::STATUS_PENDING) {
                    $order->statusHistory()->create([
                        'status' => $status,
                        'comment' => 'تم تحديث حالة الطلب إلى ' . Order::STATUSES[$status],
                        'created_at' => (clone $createdAt)->addHours(rand(1, 24)),
                        'updated_at' => (clone $createdAt)->addHours(rand(1, 24)),
                    ]);
                }

                // Save again to make sure the created_at overrides the boot method of the model if any
                $order->update([
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
                
                $order->recalculateTotals();
            }
        }

        $this->command->info('تم إنشاء الطلبات الوهمية بنجاح!');
    }
}
