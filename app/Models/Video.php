<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Video extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'video_path',
        'status',
        'slug'
    ];

    protected $appends = ['video_url'];

    protected static function booted()
    {
        static::creating(function ($video) {
            if (!$video->slug) {
                $video->slug = Str::uuid();
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function likes()
    {
        return $this->hasMany(VideoLike::class);
    }

    public function comments()
    {
        return $this->hasMany(VideoComment::class);
    }

    public function getVideoUrlAttribute()
    {
        return asset('storage/' . $this->video_path);
    }
}