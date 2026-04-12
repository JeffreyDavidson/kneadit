<?php

namespace App\Models\Engagement;

use App\Enums\Marketing\EmailDeliveryStatus;
use Database\Factories\Engagement\EmailCampaignLogFactory;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $campaign_id
 * @property string $tenant_id
 * @property string $email
 * @property EmailDeliveryStatus $status
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
#[Connection('central')]
#[Fillable('campaign_id', 'tenant_id', 'email', 'status', 'sent_at', 'opened_at')]
class EmailCampaignLog extends Model
{
    /** @use HasFactory<EmailCampaignLogFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => EmailDeliveryStatus::class,
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

    protected static function newFactory(): EmailCampaignLogFactory
    {
        return EmailCampaignLogFactory::new();
    }
}
