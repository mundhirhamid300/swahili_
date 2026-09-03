<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin','admin','student') NOT NULL DEFAULT 'student'");

        if (! DB::table('users')->where('role', 'super_admin')->exists()) {
            $firstAdmin = DB::table('users')
                ->where('role', 'admin')
                ->where('status', 'active')
                ->orderBy('id')
                ->value('id');

            if ($firstAdmin) {
                DB::table('users')->where('id', $firstAdmin)->update(['role' => 'super_admin']);
            }
        }
    }

    public function down(): void
    {
        DB::table('users')->where('role', 'super_admin')->update(['role' => 'admin']);
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','student') NOT NULL DEFAULT 'student'");
    }
};
