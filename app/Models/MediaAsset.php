<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class MediaAsset extends Model
{
    protected $fillable = [
        'user_id',
        'disk',
        'path',
        'original_name',
        'file_type',
        'mime_type',
        'size_bytes',
        'width',
        'height',
        'duration_seconds',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'size_bytes' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
            'duration_seconds' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function invitationMedia(): HasMany
    {
        return $this->hasMany(InvitationMedia::class);
    }

    public function invitations(): BelongsToMany
    {
        return $this->belongsToMany(
            Invitation::class,
            'invitation_media'
        )->withPivot([
            'id',
            'role',
            'sort_order',
            'is_primary',
            'metadata',
        ])->withTimestamps();
    }
}