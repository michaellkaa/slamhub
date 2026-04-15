<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->enum('event_mode', ['regular', 'league'])->default('regular')->after('cover_image');
            $table->boolean('is_award_event')->default(false)->after('event_mode');
            $table->foreignId('winner_award_id')->nullable()->after('is_award_event')->constrained('awards')->nullOnDelete();
            $table->json('league_data')->nullable()->after('winner_award_id');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropConstrainedForeignId('winner_award_id');
            $table->dropColumn(['event_mode', 'is_award_event', 'league_data']);
        });
    }
};
