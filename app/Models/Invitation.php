<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Invitation extends Model
{
    protected $fillable = [
        'user_id',
        'template_id',
        'title',
        'slug',
        'partner_1_name',
        'partner_2_name',
        'partner_1_parents',
        'partner_2_parents',
        'quote',
        'story',
        'status',
        'published_at',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'settings' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(InvitationEvent::class);
    }

    public function invitationMedia(): HasMany
    {
        return $this->hasMany(InvitationMedia::class);
    }

    public function mediaAssets(): BelongsToMany
    {
        return $this->belongsToMany(
            MediaAsset::class,
            'invitation_media'
        )->withPivot([
            'id',
            'role',
            'sort_order',
            'is_primary',
            'metadata',
        ])->withTimestamps();
    }

    public function guests(): HasMany
    {
        return $this->hasMany(InvitationGuest::class);
    }

    public function rsvps(): HasMany
    {
        return $this->hasMany(Rsvp::class);
    }

    public function guestbookMessages(): HasMany
    {
        return $this->hasMany(GuestbookMessage::class);
    }
}