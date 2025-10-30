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
        Schema::create('attributes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();                // رمز برمجي داخلي (مثال: color, size)
            $table->string('name');                          // اسم للعرض (مثال: اللون)
            $table->enum('input_type', [
                'text',
                'number',
                'select',
                'radio',
                'boolean',
                'date'
            ])->default('select');                           // نوع الإدخال
            $table->boolean('is_required')->default(false);  // مطلوبة لإنشاء المتغيرات؟
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attributes');
    }
};
