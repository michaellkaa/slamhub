<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventVoteSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'code',
        'enabled',
        'status',
        'current_round_id',
        'winner_round_id',
        'winner_user_id',
        'finalized_at',
        'created_by',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'finalized_at' => 'datetime',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function rounds(): HasMany
    {
        return $this->hasMany(EventVoteRound::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(EventVoteParticipant::class);
    }

    public function currentRound(): BelongsTo
    {
        return $this->belongsTo(EventVoteRound::class, 'current_round_id');
    }
}
