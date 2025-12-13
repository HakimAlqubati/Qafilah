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
        Schema::table('attribute_values', function (Blueprint $table) {
            // إضافة حقل الكود (مثل hex color code، SKU، إلخ)
            $table->string('code', 100)
                ->nullable()
                ->after('value')
                ->comment('Code for the attribute value (e.g., hex color, SKU)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attribute_values', function (Blueprint $table) {
            $table->dropColumn('code');
        });
    }
};
