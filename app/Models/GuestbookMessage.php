<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuestbookMessage extends Model
{
    protected $fillable = [
        'invitation_id',
        'invitation_guest_id',
        'sender_name',
        'message',
        'status',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
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