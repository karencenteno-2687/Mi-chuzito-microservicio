<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supply extends Model
{
    protected $fillable = [
        'name',
        'category',
        'unit',
        'cost',
        'stock',
        'minimum_stock',
        'is_available',
    ];

    protected $casts = [
        'cost'          => 'decimal:2',
        'stock'         => 'integer',
        'minimum_stock' => 'integer',
        'is_available'  => 'boolean',
    ];
}