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
            $table->integer('estimated_delivery_value')->default(24);
            $table->enum('estimated_delivery_unit', ['hours', 'days'])->default('hours');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipping_policies', function (Blueprint $table) {
            $table->dropColumn(['estimated_delivery_value', 'estimated_delivery_unit']);
        });
    }
};
