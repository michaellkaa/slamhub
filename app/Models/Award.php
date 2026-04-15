<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Award extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'icon_path',
        'created_by',
    ];


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
