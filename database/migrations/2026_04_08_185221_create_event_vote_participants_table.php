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
        Schema::create('event_vote_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_vote_session_id')->constrained('event_vote_sessions')->cascadeOnDelete();
            $table->string('participant_token', 64);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('nickname', 60)->nullable();
            $table->boolean('is_anonymous')->default(true);
            $table->timestamps();

            $table->unique(['event_vote_session_id', 'participant_token'], 'ev_participant_unique_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_vote_participants');
    }
};
