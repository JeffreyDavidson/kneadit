<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Recipe extends Model
{
    protected $fillable = [
        'product_id',
        'name',
        'ingredients',
        'instructions',
        'prep_time_minutes',
        'cost',
    ];

    protected $casts = [
        'ingredients' => 'json',
        'cost' => 'decimal:2',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
