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
        Schema::table('merchant_loyalty_settings', function (Blueprint $table) {
            $table->dropColumn('redemption_points_block');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('merchant_loyalty_settings', function (Blueprint $table) {
            $table->integer('redemption_points_block')->after('earning_reward_points');
        });
    }
};
