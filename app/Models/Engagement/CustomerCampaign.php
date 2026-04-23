<?php

namespace App\Models\Engagement;

use App\Enums\Marketing\CustomerCampaignStatus;
use Database\Factories\Engagement\CustomerCampaignFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $subject
 * @property string $body
 * @property string $target_segment 'all' or RfmSegment value
 * @property CustomerCampaignStatus $status
 * @property Carbon|null $scheduled_at
 * @property Carbon|null $sent_at
 * @property int $recipient_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerCampaign newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerCampaign newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerCampaign query()
 *
 * @mixin \Eloquent
 */
#[Fillable('name', 'subject', 'body', 'target_segment', 'status', 'scheduled_at', 'sent_at', 'recipient_count')]
#[UseFactory(CustomerCampaignFactory::class)]
class CustomerCampaign extends Model
{
    /** @use HasFactory<CustomerCampaignFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        // When a baker sets scheduled_at on a still-draft campaign, promote
        // status to Scheduled so the scheduled command picks it up. Doesn't
        // touch already-Sending/Sent campaigns.
        static::saving(function (CustomerCampaign $campaign): void {
            if ($campaign->status === CustomerCampaignStatus::Draft && $campaign->scheduled_at !== null) {
                $campaign->status = CustomerCampaignStatus::Scheduled;
            }

            if ($campaign->status === CustomerCampaignStatus::Scheduled && $campaign->scheduled_at === null) {
                $campaign->status = CustomerCampaignStatus::Draft;
            }
        });
    }

    protected function casts(): array
    {
        return [
            'status' => CustomerCampaignStatus::class,
            'scheduled_at' => 'datetime',
            'sent_at' => 'datetime',
            'recipient_count' => 'integer',
        ];
    }
}
