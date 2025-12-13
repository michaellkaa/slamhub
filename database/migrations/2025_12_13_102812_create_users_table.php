<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            // Identita
            $table->string('username')->unique();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();

            // Osobní info
            $table->date('birthdate')->nullable();

            // Autentizace
            $table->string('password')->nullable(); // null = Google login
            $table->rememberToken();

            // Profil
            $table->string('profile_pic')->nullable();

            // Role
            $table->enum('role', [
                'user',
                'performer',
                'organizer',
                'moderator',
                'admin'
            ])->default('user');

            // Stav účtu
            $table->boolean('is_banned')->default(false);
            $table->timestamp('last_login_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
