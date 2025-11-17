<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasAttributes;

class Equipment extends Model
{
    use HasFactory, HasAttributes;

    protected $fillable = [
        'name',
        'serial',
        'status', // available|allocated|maintenance
        'room_id',
        'staff_id'
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }
}
