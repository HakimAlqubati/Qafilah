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
        Schema::table('product_vendor_skus', function (Blueprint $table) {
            $table->foreignId('product_id')->nullable()->after('vendor_id')->constrained()->cascadeOnDelete();
        });

        // Data Migration for existing data
        \Illuminate\Support\Facades\DB::statement("
            UPDATE product_vendor_skus pvs
            JOIN product_variants pv ON pvs.variant_id = pv.id
            SET pvs.product_id = pv.product_id
        ");

        Schema::table('product_vendor_skus', function (Blueprint $table) {
            $table->foreignId('product_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_vendor_skus', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropColumn('product_id');
        });
    }
};
