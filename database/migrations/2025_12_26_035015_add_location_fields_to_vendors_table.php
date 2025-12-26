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
        Schema::table('vendors', function (Blueprint $table) {
            $table->foreignId('country_id')
                ->nullable()
                ->after('parent_id')
                ->constrained('countries')
                ->nullOnDelete();

            $table->foreignId('city_id')
                ->nullable()
                ->after('country_id')
                ->constrained('cities')
                ->nullOnDelete();

            $table->foreignId('district_id')
                ->nullable()
                ->after('city_id')
                ->constrained('districts')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
            $table->dropForeign(['city_id']);
            $table->dropForeign(['district_id']);
            $table->dropColumn(['country_id', 'city_id', 'district_id']);
        });
    }
};
