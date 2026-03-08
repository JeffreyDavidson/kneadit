<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductWaitlist extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'product_id',
        'customer_email',
        'customer_name',
        'notified_at',
        'created_at',
    ];

    protected $casts = [
        'notified_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    protected $table = 'product_waitlists';

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
