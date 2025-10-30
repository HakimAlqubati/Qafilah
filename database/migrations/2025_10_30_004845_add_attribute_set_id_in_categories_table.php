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
        Schema::table('categories', function (Blueprint $table) {
            $table->foreignId('attribute_set_id')->nullable()->constrained('attribute_sets')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            // 1. Drop the Foreign Key Constraint first
            // Laravel automatically names the foreign key 'categories_attribute_set_id_foreign'
            $table->dropForeign(['attribute_set_id']);

            // 2. Drop the column itself
            $table->dropColumn('attribute_set_id');
        });
    }
};
