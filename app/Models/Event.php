<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'sport_id',
        'title',
        'description',
        'starts_at',
        'duration_minutes',
        'city',
        'venue',
        'capacity',
    ];
}
