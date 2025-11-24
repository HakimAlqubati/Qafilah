<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            // 📍 الموقع الجغرافي
            if (!Schema::hasColumn('vendors', 'latitude')) {
                $table->decimal('latitude', 10, 8)->nullable()->after('logo_path');
            }
            if (!Schema::hasColumn('vendors', 'longitude')) {
                $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            }

            // 🚚 إعدادات التوصيل
            if (!Schema::hasColumn('vendors', 'delivery_rate_per_km')) {
                $table->decimal('delivery_rate_per_km', 8, 2)->default(0)->after('longitude'); // سعر التوصيل لكل كم
            }
            if (!Schema::hasColumn('vendors', 'min_delivery_charge')) {
                $table->decimal('min_delivery_charge', 8, 2)->default(0)->after('delivery_rate_per_km'); // الحد الأدنى لرسوم التوصيل
            }
            if (!Schema::hasColumn('vendors', 'max_delivery_distance')) {
                $table->integer('max_delivery_distance')->nullable()->after('min_delivery_charge'); // أقصى مسافة للتوصيل (كم)
            }

            // 💰 العملة الافتراضية
            if (!Schema::hasColumn('vendors', 'default_currency_id')) {
                $table->foreignId('default_currency_id')
                    ->nullable()
                    ->after('max_delivery_distance')
                    ->constrained('currencies')
                    ->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropForeign(['default_currency_id']);
            $table->dropColumn([
                'latitude',
                'longitude',
                'delivery_rate_per_km',
                'min_delivery_charge',
                'max_delivery_distance',
                'default_currency_id',
            ]);
        });
    }
};
