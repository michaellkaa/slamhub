<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Award extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'icon_path',
        'created_by',
    ];

    protected $appends = ['icon_url'];

    public function getIconUrlAttribute()
    {
        if (!$this->icon_path) {
            return asset('images/default-award.png'); 
        }

        if (filter_var($this->icon_path, FILTER_VALIDATE_URL)) {
            return $this->icon_path;
        }

        return asset('storage/' . $this->icon_path);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'award_user')
            ->withPivot('event_id')
            ->withTimestamps();
    }

    public function recipients()
    {
        return $this->users();
    }
}