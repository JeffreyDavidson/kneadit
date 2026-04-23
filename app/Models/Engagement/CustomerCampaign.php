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
 * @property Carbon|null $sent_at
 * @property int $recipient_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerCampaign newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerCampaign newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerCampaign query()
 *
 * @mixin \Eloquent
 */
#[Fillable('name', 'subject', 'body', 'target_segment', 'status', 'sent_at', 'recipient_count')]
#[UseFactory(CustomerCampaignFactory::class)]
class CustomerCampaign extends Model
{
    /** @use HasFactory<CustomerCampaignFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => CustomerCampaignStatus::class,
            'sent_at' => 'datetime',
            'recipient_count' => 'integer',
        ];
    }
}
