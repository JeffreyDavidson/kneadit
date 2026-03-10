<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmailCampaign extends Model
{
    protected $connection = 'central';

    protected $fillable = [
        'name',
        'subject',
        'body',
        'target_segment',
        'status',
        'scheduled_at',
        'sent_at',
        'recipient_count',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'recipient_count' => 'integer',
    ];

    public function logs(): HasMany
    {
        return $this->hasMany(EmailCampaignLog::class, 'campaign_id');
    }
}
