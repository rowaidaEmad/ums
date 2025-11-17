<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',      // classroom | lab
        'location',
        'capacity'
    ];

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function equipment()
    {
        return $this->hasMany(Equipment::class);
    }
}
