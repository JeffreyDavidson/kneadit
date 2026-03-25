<?php

namespace App\Models;

use App\DataTransferObjects\CustomerMetrics;
use App\Services\CustomerIntelligence;
use App\Traits\LogsActivity;
use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property-read Collection<int, CustomerFavorite> $customerFavorites
 * @property-read int|null $customer_favorites_count
 * @property-read Collection<int, CustomerNote> $customerNotes
 * @property-read int|null $customer_notes_count
 * @property-read Collection<int, CustomerPhoto> $customerPhotos
 * @property-read int|null $customer_photos_count
 * @property-read CustomerProfile|null $customerProfile
 * @property-read Collection<int, CustomerReminder> $customerReminders
 * @property-read int|null $customer_reminders_count
 * @property-read float $average_order_value
 * @property-read int|null $days_since_last_order
 * @property-read string $full_address
 * @property-read bool $is_at_risk
 * @property-read Carbon|null $last_order_date
 * @property-read int $lifetime_points_earned
 * @property-read float $lifetime_value
 * @property-read int $order_count
 * @property-read int $total_points
 * @property-read Collection<int, LoyaltyPoint> $loyaltyPoints
 * @property-read int|null $loyalty_points_count
 * @property-read Collection<int, Order> $orders
 * @property-read int|null $orders_count
 *
 * @method static \Database\Factories\CustomerFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer query()
 *
 * @property-read float|null $orders_sum_total
 * @property-read string|null $last_order_at
 * @property-read float|null $total_spend
 * @property Carbon|null $birthday
 *
 * @mixin \Eloquent
 */
class Customer extends Model
{
    /** @use HasFactory<CustomerFactory> */
    use HasFactory;

    use LogsActivity;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'zip',
        'notes',
        'birthday',
    ];

    protected function casts(): array
    {
        return [
            'birthday' => 'date',
        ];
    }

    /**
     * @return HasMany<Order, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * @return HasMany<CustomerNote, $this>
     */
    public function customerNotes(): HasMany
    {
        return $this->hasMany(CustomerNote::class);
    }

    /**
     * @return HasMany<LoyaltyPoint, $this>
     */
    public function loyaltyPoints(): HasMany
    {
        return $this->hasMany(LoyaltyPoint::class);
    }

    /**
     * @return HasMany<CustomerReminder, $this>
     */
    public function customerReminders(): HasMany
    {
        return $this->hasMany(CustomerReminder::class);
    }

    /**
     * @return HasMany<CustomerPhoto, $this>
     */
    public function customerPhotos(): HasMany
    {
        return $this->hasMany(CustomerPhoto::class, 'customer_email', 'email');
    }

    /**
     * @return HasMany<CustomerFavorite, $this>
     */
    public function customerFavorites(): HasMany
    {
        return $this->hasMany(CustomerFavorite::class, 'customer_email', 'email');
    }

    /**
     * @return HasOne<CustomerProfile, $this>
     */
    public function customerProfile(): HasOne
    {
        return $this->hasOne(CustomerProfile::class);
    }

    /**
     * Memoized per-instance. Uses app() because models cannot use constructor injection.
     * Prefer CustomerIntelligence::enrichQuery() for list views to avoid per-row queries.
     */
    private function getMetrics(): CustomerMetrics
    {
        return once(fn () => resolve(CustomerIntelligence::class)->metrics($this));
    }

    protected function getTotalPointsAttribute(): int
    {
        return $this->getMetrics()->totalPoints;
    }

    protected function getLifetimePointsEarnedAttribute(): int
    {
        return $this->getMetrics()->lifetimePointsEarned;
    }

    protected function getLifetimeValueAttribute(): float
    {
        return $this->getMetrics()->lifetimeValue;
    }

    protected function getOrderCountAttribute(): int
    {
        return $this->getMetrics()->orderCount;
    }

    protected function getLastOrderDateAttribute(): ?Carbon
    {
        return $this->getMetrics()->lastOrderDate;
    }

    protected function getAverageOrderValueAttribute(): float
    {
        return $this->getMetrics()->averageOrderValue;
    }

    protected function getDaysSinceLastOrderAttribute(): ?int
    {
        return $this->getMetrics()->daysSinceLastOrder;
    }

    protected function getIsAtRiskAttribute(): bool
    {
        return $this->getMetrics()->isAtRisk;
    }

    public function hasBirthday(): bool
    {
        return $this->birthday !== null;
    }

    public function isBirthdayThisMonth(): bool
    {
        return $this->birthday?->month === now()->month;
    }

    public function isBirthdayToday(): bool
    {
        return $this->birthday?->format('m-d') === now()->format('m-d');
    }

    public function daysUntilBirthday(): ?int
    {
        if (! $this->birthday) {
            return null;
        }
        $next = $this->birthday->copy()->year(now()->year);
        if ($next->isPast()) {
            $next->addYear();
        }

        return (int) now()->diffInDays($next, false);
    }

    protected function getFullAddressAttribute(): string
    {
        return collect([$this->address, $this->city, $this->state])
            ->filter()
            ->implode(', ').($this->zip ? " {$this->zip}" : '');
    }
}
