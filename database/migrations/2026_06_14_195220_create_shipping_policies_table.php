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
        Schema::create('shipping_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->unique()->constrained()->onDelete('cascade');
            $table->boolean('is_free')->default(false);
            $table->enum('charge_type', ['fixed', 'variable'])->nullable();
            $table->decimal('fixed_amount', 10, 2)->nullable();
            $table->decimal('per_km_rate', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipping_policies');
    }
};
