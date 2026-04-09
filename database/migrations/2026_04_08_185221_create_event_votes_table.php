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
        Schema::create('event_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_vote_round_id')->constrained('event_vote_rounds')->cascadeOnDelete();
            $table->foreignId('event_vote_participant_id')->constrained('event_vote_participants')->cascadeOnDelete();
            $table->unsignedTinyInteger('vote_value')->default(1);
            $table->timestamp('cast_at');
            $table->timestamps();

            $table->unique(['event_vote_round_id', 'event_vote_participant_id'], 'ev_vote_unique_per_round');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_votes');
    }
};
