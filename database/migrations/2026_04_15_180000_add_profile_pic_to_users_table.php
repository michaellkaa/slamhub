<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'profile_pic')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('profile_pic')->nullable()->after('remember_token');
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('users', 'profile_pic')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('profile_pic');
        });
    }
};
