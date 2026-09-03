<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('courses')->whereIn('title', [
            'Beginner Kiswahili',
            'Intermediate Kiswahili',
        ])->delete();
    }

    public function down(): void
    {
        // Obsolete level placeholders are intentionally not recreated.
    }
};
