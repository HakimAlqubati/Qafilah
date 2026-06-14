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
        Schema::table('shipping_policies', function (Blueprint $table) {
            $table->decimal('min_order_amount', 10, 2)->nullable()->after('is_free');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipping_policies', function (Blueprint $table) {
            $table->dropColumn('min_order_amount');
        });
    }
};
