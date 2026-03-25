<?php

namespace App\Models;

use App\Enums\EmailCampaignStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmailCampaign extends Model
{
    use HasFactory;

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

    protected function casts(): array
    {
        return [
            'status' => EmailCampaignStatus::class,
            'scheduled_at' => 'datetime',
            'sent_at' => 'datetime',
            'recipient_count' => 'integer',
        ];
    }

    /**
     * @return HasMany<EmailCampaignLog, $this>
     */
    public function logs(): HasMany
    {
        return $this->hasMany(EmailCampaignLog::class, 'campaign_id');
    }
}
