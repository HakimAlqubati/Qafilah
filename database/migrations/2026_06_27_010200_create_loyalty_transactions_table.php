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
        Schema::create('loyalty_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')
                ->constrained('customer_loyalty_wallets')
                ->cascadeOnDelete();
            
            // Reference to the order that triggered this transaction
            $table->foreignId('order_id')
                ->nullable()
                ->constrained('orders')
                ->nullOnDelete();

            $table->string('type')->index(); // 'earned', 'redeemed', 'refunded'
            $table->integer('points'); // Signed integer (positive for earning, negative for redemption/refund deduction)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loyalty_transactions');
    }
};
