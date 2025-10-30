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
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            
            // Core Vendor Data
            $table->string('name')->index(); 
            $table->string('slug')->unique();
            $table->string('contact_person')->nullable();
            $table->string('email')->nullable()->unique(); // UNIQUE added for better data integrity
            $table->string('phone')->nullable();
            $table->string('vat_id', 50)->nullable()->unique(); // B2B essential field
            $table->enum('status', ['active', 'inactive', 'pending'])->default('pending')->index();
            $table->text('description')->nullable();
            $table->string('logo_path')->nullable();
            
            // 🚨 Audit Fields (Added)
            // Relate to the user who created the record (Admin/Platform User)
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            // Relate to the user who last updated the record
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->softDeletes();
            $table->timestamps();
            
            // Composite Index for efficient vendor status check and lookup
            $table->index(['status', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};