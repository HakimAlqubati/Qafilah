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
        Schema::create('merchant_loyalty_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchant_id')
                ->unique()
                ->constrained('vendors')
                ->cascadeOnDelete();
            $table->boolean('is_active')->default(false);
            $table->decimal('earning_spend_amount', 8, 2);
            $table->integer('earning_reward_points');
            $table->integer('redemption_points_block');
            $table->decimal('redemption_discount_value', 8, 2);
            $table->integer('min_points_to_redeem');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('merchant_loyalty_settings');
    }
};
