<?php

namespace App\Models\Engagement;

use App\Enums\Marketing\EmailCampaignSegment;
use App\Enums\Marketing\EmailCampaignStatus;
use Database\Factories\Engagement\EmailCampaignFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $subject
 * @property string $body
 * @property EmailCampaignSegment $target_segment
 * @property EmailCampaignStatus $status
 * @property Carbon|null $scheduled_at
 * @property Carbon|null $sent_at
 * @property int $recipient_count
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, EmailCampaignLog> $logs
 * @property-read int|null $logs_count
 *
 * @method static \Database\Factories\EmailCampaignFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailCampaign newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailCampaign newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailCampaign query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailCampaign whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailCampaign whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailCampaign whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailCampaign whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailCampaign whereRecipientCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailCampaign whereScheduledAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailCampaign whereSentAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailCampaign whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailCampaign whereSubject($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailCampaign whereTargetSegment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailCampaign whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class EmailCampaign extends Model
{
    /** @use HasFactory<EmailCampaignFactory> */
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
            'target_segment' => EmailCampaignSegment::class,
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

    protected static function newFactory(): EmailCampaignFactory
    {
        return EmailCampaignFactory::new();
    }
}
