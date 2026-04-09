<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventVote extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'event_vote_round_id',
        'event_vote_participant_id',
        'vote_value',
        'cast_at',
    ];

    protected $casts = [
        'cast_at' => 'datetime',
    ];

    public function round(): BelongsTo
    {
        return $this->belongsTo(EventVoteRound::class, 'event_vote_round_id');
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(EventVoteParticipant::class, 'event_vote_participant_id');
    }
}
