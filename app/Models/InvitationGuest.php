<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InvitationGuest extends Model
{
    protected $fillable = [
        'invitation_id',
        'guest_name',
        'phone',
        'email',
        'invitation_token',
        'max_attendees',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'max_attendees' => 'integer',
        ];
    }

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }

    public function rsvp(): HasOne
    {
        return $this->hasOne(Rsvp::class);
    }

    public function guestbookMessages(): HasMany
    {
        return $this->hasMany(GuestbookMessage::class);
    }
}