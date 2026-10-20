<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug', // depend
        'plan',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class)->withPivot('role', 'joined_at');
        // belongsToMany = organization that can have multiple user and inverse
        // withPivot = get role and joined_at
    }
}
