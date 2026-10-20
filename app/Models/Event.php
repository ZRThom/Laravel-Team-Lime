<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
