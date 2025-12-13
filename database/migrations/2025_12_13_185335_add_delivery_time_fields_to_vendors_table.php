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
            // مدة التوصيل المتوقعة
            $table->unsignedInteger('delivery_time_value')
                ->nullable()
                ->after('max_delivery_distance')
                ->comment('Delivery time value (e.g., 1, 2, 24, 48)');

            $table->enum('delivery_time_unit', ['hours', 'days'])
                ->nullable()
                ->after('delivery_time_value')
                ->comment('Delivery time unit (hours or days)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn(['delivery_time_value', 'delivery_time_unit']);
        });
    }
};
