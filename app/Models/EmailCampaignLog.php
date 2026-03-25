<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $campaign_id
 * @property string $tenant_id
 * @property string $email
 * @property string $status
 * @property Carbon|null $sent_at
 * @property Carbon|null $opened_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read EmailCampaign $campaign
 *
 * @method static \Database\Factories\EmailCampaignLogFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailCampaignLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailCampaignLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailCampaignLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailCampaignLog whereCampaignId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailCampaignLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailCampaignLog whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailCampaignLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailCampaignLog whereOpenedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailCampaignLog whereSentAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailCampaignLog whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailCampaignLog whereTenantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmailCampaignLog whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class EmailCampaignLog extends Model
{
    use HasFactory;

    protected $connection = 'central';

    protected $fillable = [
        'campaign_id',
        'tenant_id',
        'email',
        'status',
        'sent_at',
        'opened_at',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'opened_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<EmailCampaign, $this>
     */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(EmailCampaign::class, 'campaign_id');
    }
}
