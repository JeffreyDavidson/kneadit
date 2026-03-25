<?php

namespace App\Models;

use App\DataTransferObjects\CustomerMetrics;
use App\Services\CustomerIntelligence;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

class Customer extends Model
{
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
        return once(fn () => app(CustomerIntelligence::class)->metrics($this));
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
