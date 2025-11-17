<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerformanceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'period',
        'score',
        'notes'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
