<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'entity_type', // e.g., equipment, course, room, user
        'data_type'    // string|text|int|bool|date
    ];

    public function values()
    {
        return $this->hasMany(AttributeValue::class);
    }
}
