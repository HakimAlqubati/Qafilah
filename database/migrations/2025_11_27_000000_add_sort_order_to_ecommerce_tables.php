<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // categories
        Schema::table('categories', function (Blueprint $table) {
            $table->unsignedInteger('sort_order')
                ->default(0)
                ->after('parent_id');
        });

        // attributes
        Schema::table('attributes', function (Blueprint $table) {
            $table->unsignedInteger('sort_order')
                ->default(0)
                ->after('name');
        });

        // units
        Schema::table('units', function (Blueprint $table) {
            $table->unsignedInteger('sort_order')
                ->default(0)
                ->after('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });

        Schema::table('attributes', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });

        Schema::table('units', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
    }
};
