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
            // نضيف العمود الجديد بعد العمود "sort_order" إن وجد
            if (!Schema::hasColumn('attribute_values', 'is_active')) {
                $table->boolean('is_active')
                    ->default(true)
                    ->after('sort_order')
                    ->comment('Indicates whether this attribute value is active or hidden');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attribute_values', function (Blueprint $table) {
            if (Schema::hasColumn('attribute_values', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });
    }
};
