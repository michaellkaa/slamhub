<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'username')) {
                $table->string('username')->nullable()->unique()->after('id');
            }

            if (!Schema::hasColumn('users', 'role')) {
                $table->enum('role', ['user', 'performer', 'organizer', 'moderator', 'admin'])
                    ->default('user')
                    ->after('remember_token');
            }

            if (!Schema::hasColumn('users', 'is_banned')) {
                $table->boolean('is_banned')->default(false)->after('role');
            }

            if (!Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->after('is_banned');
            }

            if (!Schema::hasColumn('users', 'google_id')) {
                $table->string('google_id')->nullable()->after('password');
            }
        });
    }

    public function down(): void
    {
        // Intentionally left empty to avoid dropping columns
        // that might pre-exist in environments with different migration history.
    }
};
