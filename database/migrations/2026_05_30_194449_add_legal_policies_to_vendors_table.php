<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->longText('terms_and_conditions')->nullable()->after('description');
            $table->longText('privacy_policy')->nullable()->after('terms_and_conditions');
            $table->longText('store_policy')->nullable()->after('privacy_policy');
            $table->longText('return_policy')->nullable()->after('store_policy');
        });
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn([
                'terms_and_conditions',
                'privacy_policy',
                'store_policy',
                'return_policy',
            ]);
        });
    }
};
