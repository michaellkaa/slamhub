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
        Schema::create('event_vote_rounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_vote_session_id')->constrained('event_vote_sessions')->cascadeOnDelete();
            $table->foreignId('performer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('performer_name');
            $table->enum('state', ['pending', 'live', 'closed'])->default('pending');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
        });

        Schema::table('event_vote_sessions', function (Blueprint $table) {
            $table->foreign('current_round_id')
                ->references('id')
                ->on('event_vote_rounds')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_vote_sessions', function (Blueprint $table) {
            $table->dropForeign(['current_round_id']);
        });

        Schema::dropIfExists('event_vote_rounds');
    }
};
