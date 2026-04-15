<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_vote_rounds', function (Blueprint $table) {
            $table->boolean('include_in_ranking')->default(true)->after('state');
            $table->unsignedInteger('total_score')->default(0)->after('include_in_ranking');
            $table->unsignedInteger('votes_count')->default(0)->after('total_score');
        });

        Schema::table('event_vote_sessions', function (Blueprint $table) {
            $table->foreignId('winner_round_id')->nullable()->after('current_round_id')->constrained('event_vote_rounds')->nullOnDelete();
            $table->foreignId('winner_user_id')->nullable()->after('winner_round_id')->constrained('users')->nullOnDelete();
            $table->timestamp('finalized_at')->nullable()->after('winner_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('event_vote_sessions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('winner_round_id');
            $table->dropConstrainedForeignId('winner_user_id');
            $table->dropColumn('finalized_at');
        });

        Schema::table('event_vote_rounds', function (Blueprint $table) {
            $table->dropColumn(['include_in_ranking', 'total_score', 'votes_count']);
        });
    }
};
