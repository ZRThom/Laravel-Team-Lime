<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'sport_id',
        'created_by',
        'title',
        'description',
        'starts_at',
        'duration_minutes',
        'city',
        'venue',
        'capacity',
        'public_token',
        'status',
    ];

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'event_participants')
            ->withPivot('status', 'registered_at');
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function isOwnedBy(User $user): bool
    {
        return $this->organization->users()->where('users.id', $user->id)->wherePivot('role', 'owner')->exists();
    }
}
