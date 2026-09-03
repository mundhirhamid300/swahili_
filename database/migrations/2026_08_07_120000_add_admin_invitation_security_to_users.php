<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('invitation_token_hash', 64)->nullable()->unique()->after('remember_token');
            $table->timestamp('invitation_expires_at')->nullable()->after('invitation_token_hash');
            $table->timestamp('invitation_accepted_at')->nullable()->after('invitation_expires_at');
            $table->boolean('must_change_password')->default(false)->after('invitation_accepted_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['invitation_token_hash']);
            $table->dropColumn([
                'invitation_token_hash',
                'invitation_expires_at',
                'invitation_accepted_at',
                'must_change_password',
            ]);
        });
    }
};
