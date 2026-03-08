<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurveyResponse extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'survey_id',
        'customer_name',
        'customer_email',
        'answers',
        'order_id',
        'created_at',
    ];

    protected $casts = [
        'answers' => 'array',
        'created_at' => 'datetime',
    ];

    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
