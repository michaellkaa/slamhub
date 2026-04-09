<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventVoteRound extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_vote_session_id',
        'performer_id',
        'performer_name',
        'state',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(EventVoteSession::class, 'event_vote_session_id');
    }

    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performer_id');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(EventVote::class, 'event_vote_round_id');
    }
}
