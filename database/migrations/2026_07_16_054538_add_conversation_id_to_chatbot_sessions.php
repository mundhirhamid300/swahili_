<?php

use App\Models\ChatbotSession;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chatbot_sessions', function (Blueprint $table) {
            $table->uuid('conversation_id')->nullable()->after('user_id')->index();
        });

        // Group each user's existing messages into one past conversation.
        ChatbotSession::query()
            ->whereNull('conversation_id')
            ->orderBy('user_id')
            ->orderBy('id')
            ->get()
            ->groupBy('user_id')
            ->each(function ($rows) {
                $conversationId = (string) Str::uuid();
                ChatbotSession::whereIn('id', $rows->pluck('id'))
                    ->update(['conversation_id' => $conversationId]);
            });
    }

    public function down(): void
    {
        Schema::table('chatbot_sessions', function (Blueprint $table) {
            $table->dropIndex(['conversation_id']);
            $table->dropColumn('conversation_id');
        });
    }
};
