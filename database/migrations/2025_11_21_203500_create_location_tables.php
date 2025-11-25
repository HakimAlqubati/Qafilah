<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Schema::create('countries', function (Blueprint $table) {
        //     $table->id();
        //     $table->string('name');
        //     $table->string('code')->unique(); // ISO code
        //     $table->string('phone_code');
        //     $table->boolean('status')->default(true);
        //     $table->timestamps();
        // });

        // Schema::create('cities', function (Blueprint $table) {
        //     $table->id();
        //     $table->foreignId('country_id')->constrained()->cascadeOnDelete();
        //     $table->string('name');
        //     $table->boolean('status')->default(true);
        //     $table->timestamps();
        // });

        // Schema::create('districts', function (Blueprint $table) {
        //     $table->id();
        //     $table->foreignId('city_id')->constrained()->cascadeOnDelete();
        //     $table->string('name');
        //     $table->boolean('status')->default(true);
        //     $table->timestamps();
        // });
    }

    public function down(): void
    {
        Schema::dropIfExists('districts');
        Schema::dropIfExists('cities');
        Schema::dropIfExists('countries');
    }
};
