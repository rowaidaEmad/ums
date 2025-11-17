<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
        'period_start',
        'period_end',
        'status'
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end'   => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
