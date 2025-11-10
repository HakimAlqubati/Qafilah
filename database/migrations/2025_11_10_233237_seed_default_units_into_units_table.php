<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $units = [
            ['name' => 'Set',   'description' => null, 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Piece', 'description' => null, 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Box',   'description' => null, 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'CTN',   'description' => null, 'active' => true, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'DZN',   'description' => null, 'active' => true, 'created_at' => $now, 'updated_at' => $now],
        ];

        // لإدخال السجلات وتجاهل التكرارات إن وجدت
        DB::table('units')->insertOrIgnore($units);
    }

    public function down(): void
    {
        DB::table('units')
            ->whereIn('name', ['Set', 'Piece', 'Box', 'CTN', 'DZN'])
            ->delete();
    }
};
