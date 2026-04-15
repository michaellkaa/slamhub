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
        if (!Schema::hasTable('event_vote_sessions')) {
            Schema::create('event_vote_sessions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('event_id')->constrained()->cascadeOnDelete();
                $table->string('code', 12)->unique();
                $table->boolean('enabled')->default(false);
                $table->enum('status', ['draft', 'live', 'paused', 'closed'])->default('draft');
                $table->unsignedBigInteger('current_round_id')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('event_vote_rounds')) {
            return;
        }

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

        if (!Schema::hasColumn('event_vote_sessions', 'current_round_id')) {
            Schema::table('event_vote_sessions', function (Blueprint $table) {
                $table->unsignedBigInteger('current_round_id')->nullable();
            });
        }

        Schema::table('event_vote_sessions', function (Blueprint $table) {
            try {
                $table->foreign('current_round_id')
                    ->references('id')
                    ->on('event_vote_rounds')
                    ->nullOnDelete();
            } catch (\Throwable $e) {
                // Ignore if FK already exists in environments with partial schema history.
            }
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
