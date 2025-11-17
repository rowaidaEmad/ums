<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'credits',
        'is_core'
    ];

    protected $casts = [
        'is_core' => 'boolean',
    ];

    public function assessments()
    {
        return $this->hasMany(Assessment::class);
    }
}
