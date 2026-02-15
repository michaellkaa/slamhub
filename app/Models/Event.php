<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Award;


class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'starts_at',
        'ends_at',
        'location',
        'ticket_url',
        'cover_image',
        'guest_performers',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at'   => 'datetime',
        'guest_performers' => 'array',
    ];

    public function organizer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function performers()
    {
        return $this->belongsToMany(User::class, 'event_performer');
    }

    

    public function awards()
    {
        return $this->belongsToMany(Award::class, 'award_user');
    }

}
