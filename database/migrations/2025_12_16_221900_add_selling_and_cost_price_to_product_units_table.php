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
        Schema::table('product_units', function (Blueprint $table) {
            $table->decimal('selling_price', 12, 2)->nullable()->after('conversion_factor')->comment('سعر البيع');
            $table->decimal('cost_price', 12, 2)->nullable()->after('selling_price')->comment('سعر التكلفة');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_units', function (Blueprint $table) {
            $table->dropColumn(['selling_price', 'cost_price']);
        });
    }
};
