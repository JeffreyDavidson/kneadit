<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class Customer extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'zip',
        'notes',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function customerNotes(): HasMany
    {
        return $this->hasMany(CustomerNote::class);
    }

    public function loyaltyPoints(): HasMany
    {
        return $this->hasMany(LoyaltyPoint::class);
    }

    public function getTotalPointsAttribute(): int
    {
        return (int) $this->loyaltyPoints()
            ->select(DB::raw("COALESCE(SUM(CASE WHEN type = 'earned' OR type = 'adjusted' THEN points ELSE 0 END) - SUM(CASE WHEN type = 'redeemed' THEN points ELSE 0 END), 0)"))
            ->value(DB::raw("COALESCE(SUM(CASE WHEN type = 'earned' OR type = 'adjusted' THEN points ELSE 0 END) - SUM(CASE WHEN type = 'redeemed' THEN points ELSE 0 END), 0)"));
    }

    public function getLifetimePointsEarnedAttribute(): int
    {
        return (int) $this->loyaltyPoints()->where('type', 'earned')->sum('points');
    }

    public function customerProfile(): HasOne
    {
        return $this->hasOne(CustomerProfile::class);
    }

    public function getLifetimeValueAttribute(): float
    {
        return $this->orders()->where('status', '!=', 'cancelled')->sum('total');
    }

    public function getOrderCountAttribute(): int
    {
        return $this->orders()->where('status', '!=', 'cancelled')->count();
    }

    public function getLastOrderDateAttribute(): ?Carbon
    {
        return $this->orders()->latest()->value('created_at');
    }

    public function getAverageOrderValueAttribute(): float
    {
        return $this->order_count > 0 ? $this->lifetime_value / $this->order_count : 0;
    }

    public function getDaysSinceLastOrderAttribute(): ?int
    {
        if (!$this->last_order_date) return null;
        return (int) Carbon::parse($this->last_order_date)->diffInDays(now());
    }

    public function getIsAtRiskAttribute(): bool
    {
        return $this->order_count > 0 && $this->days_since_last_order !== null && $this->days_since_last_order > 30;
    }

    public function getFullAddressAttribute(): string
    {
        $address = '';
        if ($this->address) $address .= $this->address;
        if ($this->city) $address .= ($address ? ', ' : '') . $this->city;
        if ($this->state) $address .= ($address ? ', ' : '') . $this->state;
        if ($this->zip) $address .= ($address ? ' ' : '') . $this->zip;
        return $address;
    }
}
