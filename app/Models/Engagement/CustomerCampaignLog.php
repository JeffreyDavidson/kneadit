<?php

namespace App\Models\Engagement;

use Database\Factories\Engagement\CustomerCampaignLogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $customer_campaign_id
 * @property string $customer_email
 * @property string $tracking_token
 * @property Carbon|null $opened_at
 * @property-read CustomerCampaign $campaign
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerCampaignLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerCampaignLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerCampaignLog query()
 *
 * @mixin \Eloquent
 */
#[Fillable('customer_campaign_id', 'customer_email', 'tracking_token', 'opened_at')]
#[UseFactory(CustomerCampaignLogFactory::class)]
class CustomerCampaignLog extends Model
{
    /** @use HasFactory<CustomerCampaignLogFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'opened_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<CustomerCampaign, $this> */
    public function campaign(): BelongsTo
    {
        return $this->belongsTo(CustomerCampaign::class, 'customer_campaign_id');
    }
}
