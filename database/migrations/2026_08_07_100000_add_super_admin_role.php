<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin','admin','student') NOT NULL DEFAULT 'student'");
        } elseif (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check');
            DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('super_admin', 'admin', 'student'))");
        }

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
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','student') NOT NULL DEFAULT 'student'");
        } elseif (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check');
            DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('admin', 'student'))");
        }
    }
};
