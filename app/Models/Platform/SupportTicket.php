<?php

namespace App\Models\Platform;

use App\Builders\Platform\SupportTicketQueryBuilder;
use App\Enums\Platform\SupportTicketPriority;
use App\Enums\Platform\SupportTicketStatus;
use App\Observers\Platform\SupportTicketObserver;
use Database\Factories\Platform\SupportTicketFactory;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\UseEloquentBuilder;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $tenant_id
 * @property string $subject
 * @property string $body
 * @property SupportTicketStatus $status
 * @property SupportTicketPriority $priority
 * @property string|null $admin_notes
 * @property Carbon|null $resolved_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, SupportReply> $replies
 * @property-read int|null $replies_count
 * @property-read Tenant $tenant
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupportTicket newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupportTicket newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SupportTicket query()
 *
 * @mixin \Eloquent
 */
#[Connection('central')]
#[Fillable('tenant_id', 'subject', 'body', 'status', 'priority', 'admin_notes', 'resolved_at')]
#[ObservedBy(SupportTicketObserver::class)]
#[UseEloquentBuilder(SupportTicketQueryBuilder::class)]
#[UseFactory(SupportTicketFactory::class)]
class SupportTicket extends Model
{
    /** @use HasFactory<SupportTicketFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => SupportTicketStatus::class,
            'priority' => SupportTicketPriority::class,
            'resolved_at' => 'datetime',
        ];
    }

    /**
     * @return HasMany<SupportReply, $this>
     */
    public function replies(): HasMany
    {
        return $this->hasMany(SupportReply::class, 'ticket_id');
    }

    /**
     * @return BelongsTo<Tenant, $this>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
