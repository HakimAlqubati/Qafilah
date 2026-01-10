<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // نستخدم Transaction لضمان إدخال الكل أو لا شيء (Atomic Operation)
        DB::transaction(function () {
            
            $now = Carbon::now();

            $gateways = [
                // 1. الكريمي (Electronic)
                [
                    'name'         => 'الكريمي جوال (mFloos)',
                    'code'         => 'kurimi',
                    'type'         => 'electronic',
                    'credentials'  => json_encode(['merchant_id' => '', 'password' => '']), // يملأها الأدمن لاحقاً
                    'instructions' => null,
                    'is_active'    => true,
                    'mode'         => 'sandbox',
                    'created_at'   => $now,
                    'updated_at'   => $now,
                ],
                // 2. وان كاش (Electronic)
                [
                    'name'         => 'وان كاش (OneCash)',
                    'code'         => 'onecash',
                    'type'         => 'electronic',
                    'credentials'  => json_encode(['merchant_id' => '', 'secret_key' => '']),
                    'instructions' => null,
                    'is_active'    => true,
                    'mode'         => 'sandbox',
                    'created_at'   => $now,
                    'updated_at'   => $now,
                ],
                // 3. الدفع عند الاستلام (Cash)
                [
                    'name'         => 'الدفع عند الاستلام',
                    'code'         => 'cod',
                    'type'         => 'cash',
                    'credentials'  => null,
                    'instructions' => 'يرجى تجهيز المبلغ نقداً عند وصول المندوب.',
                    'is_active'    => true,
                    'mode'         => 'live', // الكاش دائماً Live
                    'created_at'   => $now,
                    'updated_at'   => $now,
                ],
                // 4. حوالة بنكية (Transfer)
                [
                    'name'         => 'حوالة بنكية / إيداع',
                    'code'         => 'bank_transfer',
                    'type'         => 'transfer',
                    'credentials'  => null,
                    'instructions' => "بنك الكريمي: 123456 - باسم: متجر قافلة\nبنك التضامن: 987654 - باسم: متجر قافلة\n\nيرجى إرفاق صورة الإيصال بعد التحويل.",
                    'is_active'    => true,
                    'mode'         => 'live',
                    'created_at'   => $now,
                    'updated_at'   => $now,
                ],
            ];

            DB::table('payment_gateways')->insert($gateways);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // حذف البيانات التي أضفناها فقط عند التراجع
        DB::table('payment_gateways')
            ->whereIn('code', ['kurimi', 'onecash', 'cod', 'bank_transfer'])
            ->delete();
    }
};