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
        Schema::create('attribute_sets', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();              // اسم القالب مثل: خصائص الملابس
            $table->text('description')->nullable();       // وصف اختياري للقالب
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attribute_sets');
    }
};
