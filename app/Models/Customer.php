<?php

namespace App\Models;

use App\Enums\LoyaltyPointType;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;

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

    protected function getTotalPointsAttribute(): int
    {
        $earned = (int) $this->loyaltyPoints()->earned()->sum('points');
        $adjusted = (int) $this->loyaltyPoints()->where('type', LoyaltyPointType::Adjusted)->sum('points');
        $redeemed = (int) $this->loyaltyPoints()->redeemed()->sum('points');

        return $earned + $adjusted - $redeemed;
    }

    protected function getLifetimePointsEarnedAttribute(): int
    {
        return (int) $this->loyaltyPoints()->earned()->sum('points');
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

    protected function getLifetimeValueAttribute(): float
    {
        return $this->orders()->active()->sum('total');
    }

    protected function getOrderCountAttribute(): int
    {
        return $this->orders()->active()->count();
    }

    protected function getLastOrderDateAttribute(): ?Carbon
    {
        return $this->orders()->latest()->value('created_at');
    }

    protected function getAverageOrderValueAttribute(): float
    {
        return $this->order_count > 0 ? $this->lifetime_value / $this->order_count : 0;
    }

    protected function getDaysSinceLastOrderAttribute(): ?int
    {
        if (! $this->last_order_date) {
            return null;
        }

        return (int) Date::parse($this->last_order_date)->diffInDays(now());
    }

    protected function getIsAtRiskAttribute(): bool
    {
        return $this->order_count > 0 && $this->days_since_last_order !== null && $this->days_since_last_order > 30;
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
        $address = '';
        if ($this->address) {
            $address .= $this->address;
        }
        if ($this->city) {
            $address .= ($address ? ', ' : '').$this->city;
        }
        if ($this->state) {
            $address .= ($address ? ', ' : '').$this->state;
        }
        if ($this->zip) {
            $address .= ($address ? ' ' : '').$this->zip;
        }

        return $address;
    }
}
