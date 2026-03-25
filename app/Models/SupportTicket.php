<?php

namespace App\Models;

use App\Enums\SupportTicketPriority;
use App\Enums\SupportTicketStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportTicket extends Model
{
    use HasFactory;

    protected $connection = 'central';

    protected $fillable = [
        'tenant_id',
        'subject',
        'body',
        'status',
        'priority',
        'admin_notes',
        'resolved_at',
    ];

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
