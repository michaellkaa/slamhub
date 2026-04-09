<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventVoteParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_vote_session_id',
        'participant_token',
        'user_id',
        'nickname',
        'is_anonymous',
    ];

    protected $casts = [
        'is_anonymous' => 'boolean',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(EventVoteSession::class, 'event_vote_session_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(EventVote::class, 'event_vote_participant_id');
    }
}
