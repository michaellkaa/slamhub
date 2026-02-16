<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Award;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'username',
        'name',
        'email',
        'birthdate',
        'password',
        'profile_pic',
        'role',
        'is_banned',
        'last_login_at',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'birthdate'         => 'date',
        'email_verified_at' => 'datetime',
        'last_login_at'     => 'datetime',
        'is_banned'         => 'boolean',
    ];

    // Přidá Accessor do JSONu
    protected $appends = ['profile_pic_url'];

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isModerator(): bool
    {
        return in_array($this->role, ['moderator', 'admin']);
    }

    public function isBanned(): bool
    {
        return $this->is_banned;
    }

    public function getProfilePicUrlAttribute(): string
    {
        return $this->profile_pic
            ? asset('storage/' . $this->profile_pic)
            : asset('images/default-avatar.png');
    }

    public function createdEvents()
    {
        return $this->hasMany(Event::class, 'user_id');
    }

    public function performedEvents()
    {
        return $this->belongsToMany(Event::class, 'event_performer');
    }

    public function performer()
    {
        return $this->hasOne(Performer::class);
    }

    public function awards()
    {
        return $this->belongsToMany(Award::class, 'award_user')
            ->withPivot('event_id')
            ->withTimestamps();
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }



}

