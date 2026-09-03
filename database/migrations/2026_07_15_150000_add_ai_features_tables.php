<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('translations_log', function (Blueprint $table) {
            $table->string('source', 40)->nullable()->after('to_lang');
            $table->string('mode', 40)->default('translate')->after('source');
            $table->unsignedInteger('tokens_used')->nullable()->after('mode');
        });

        Schema::table('chatbot_sessions', function (Blueprint $table) {
            $table->string('mode', 40)->default('tutor')->after('response');
        });

        Schema::create('ai_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_settings');

        Schema::table('chatbot_sessions', function (Blueprint $table) {
            $table->dropColumn('mode');
        });

        Schema::table('translations_log', function (Blueprint $table) {
            $table->dropColumn(['source', 'mode', 'tokens_used']);
        });
    }
};
