<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttributeValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'attribute_id',
        'entity_id',
        'value_string',
        'value_text',
        'value_int',
        'value_bool',
        'value_date'
    ];

    protected $casts = [
        'value_bool' => 'boolean',
        'value_date' => 'date',
    ];

    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }
}
