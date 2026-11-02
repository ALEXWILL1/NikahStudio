<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rsvp extends Model
{
    protected $table = 'rsvps';

    protected $fillable = [
        'invitation_id',
        'invitation_guest_id',
        'guest_name',
        'attendance_status',
        'attendee_count',
        'message',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'attendee_count' => 'integer',
            'submitted_at' => 'datetime',
        ];
    }

    public function invitation(): BelongsTo
    {
        return $this->belongsTo(Invitation::class);
    }

    public function invitationGuest(): BelongsTo
    {
        return $this->belongsTo(InvitationGuest::class);
    }
}