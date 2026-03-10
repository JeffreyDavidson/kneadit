<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailCampaignLog extends Model
{
    protected $connection = 'central';

    protected $fillable = [
        'campaign_id',
        'tenant_id',
        'email',
        'status',
        'sent_at',
        'opened_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'opened_at' => 'datetime',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(EmailCampaign::class, 'campaign_id');
    }
}
