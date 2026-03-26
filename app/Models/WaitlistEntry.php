<?php

namespace App\Models;

use App\Casts\StripTagsCast;
use App\Enums\WaitlistStatus;
use Carbon\Carbon;
use Database\Factories\WaitlistEntryFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Date;

/**
 * @property WaitlistStatus $status
 * @property-read string $status_label
 * @property-read Product|null $product
 *
 * @method static \Database\Factories\WaitlistEntryFactory factory($count = null, $state = [])
 * @method static Builder<static>|WaitlistEntry forDate(\Carbon\Carbon|string $date)
 * @method static Builder<static>|WaitlistEntry newModelQuery()
 * @method static Builder<static>|WaitlistEntry newQuery()
 * @method static Builder<static>|WaitlistEntry query()
 * @method static Builder<static>|WaitlistEntry waiting()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WaitlistEntry waiting()
 *
 * @mixin \Eloquent
 */
class WaitlistEntry extends Model
{
    /** @use HasFactory<WaitlistEntryFactory> */
    use HasFactory;

    protected $fillable = [
        'customer_name',
        'customer_email',
        'customer_phone',
        'requested_date',
        'product_id',
        'notes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'requested_date' => 'date',
            'status' => WaitlistStatus::class,
            'notes' => StripTagsCast::class,
        ];
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    protected function getStatusLabelAttribute(): string
    {
        return ucfirst($this->status->value);
    }

    /** @param Builder<WaitlistEntry> $query */
    #[Scope]
    protected function waiting(Builder $query): void
    {
        $query->where('status', WaitlistStatus::Waiting);
    }

    /** @param Builder<WaitlistEntry> $query */
    #[Scope]
    protected function forDate(Builder $query, Carbon|string $date): void
    {
        $query->whereDate('requested_date', Date::parse($date));
    }

    public function markNotified(): void
    {
        $this->update([
            'status' => WaitlistStatus::Notified,
        ]);
    }

    public function markConverted(): void
    {
        $this->update([
            'status' => WaitlistStatus::Converted,
        ]);
    }

    public function markRemoved(): void
    {
        $this->update([
            'status' => WaitlistStatus::Removed,
        ]);
    }
}
