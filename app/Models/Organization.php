<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Organization extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug', // depend
        'plan',
    ];

    public function users(): BelongsToMany
    {
    return $this->belongsToMany(User::class, 'organization_user')
        ->withPivot('role', 'joined_at');

    }    
    public function members(): BelongsToMany
    {
        return $this->users();
    }
    
}

