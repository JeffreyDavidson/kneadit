<?php

namespace App\Models\Operations;

use App\Builders\Operations\WebhookDeliveryQueryBuilder;
use Database\Factories\Operations\WebhookDeliveryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseEloquentBuilder;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookDelivery newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookDelivery newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WebhookDelivery query()
 *
 * @mixin \Eloquent
 *
 * @property array<string, mixed> $payload
 * @property \Illuminate\Support\Carbon $dispatched_at
 */
#[Fillable('event', 'url', 'payload', 'signature', 'status_code', 'response_body', 'attempt', 'succeeded', 'error', 'dispatched_at', 'responded_at')]
#[UseEloquentBuilder(WebhookDeliveryQueryBuilder::class)]
#[UseFactory(WebhookDeliveryFactory::class)]
class WebhookDelivery extends Model
{
    /** @use HasFactory<WebhookDeliveryFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'status_code' => 'integer',
            'attempt' => 'integer',
            'succeeded' => 'boolean',
            'dispatched_at' => 'datetime',
            'responded_at' => 'datetime',
        ];
    }
}
