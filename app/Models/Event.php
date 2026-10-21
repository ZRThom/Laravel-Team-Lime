<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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

    protected function casts(): array
    {
        return ['starts_at' => 'datetime'];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function sport(): BelongsTo
    {
        return $this->belongsTo(Sport::class);
    }

    // published, in the future and not full
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->where('starts_at', '>', now())
            ->whereRaw("capacity > (select count(*) from event_participants where event_participants.event_id = events.id and event_participants.status = 'confirmed')");
    }

    // optional filters (sport, city, date), each one is applied only when filled
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['sport_id'] ?? null, fn (Builder $q, $sport) => $q->where('sport_id', $sport))
            ->when($filters['city'] ?? null, fn (Builder $q, $city) => $q->where('city', 'like', '%'.$city.'%'))
            ->when($filters['date'] ?? null, fn (Builder $q, $date) => $q->whereDate('starts_at', $date));
    }

    // adds confirmed_count, used to compute the remaining seats
    public function scopeWithConfirmedCount(Builder $query): Builder
    {
        return $query->withCount(['participants as confirmed_count' => fn ($q) => $q->where('event_participants.status', 'confirmed')]);
    }

    public function remainingSeats(): int
    {
        $confirmed = $this->confirmed_count ?? $this->participants()->wherePivot('status', 'confirmed')->count();

        return $this->capacity - $confirmed;
    }

    // true if the user has a confirmed registration
    public function isRegisteredBy(User $user): bool
    {
        return $this->participants()->where('users.id', $user->id)->wherePivot('status', 'confirmed')->exists();
    }

    public function isOwnedBy(User $user): bool
    {
        return $this->organization->users()->where('users.id', $user->id)->wherePivot('role', 'owner')->exists();
    }
}
