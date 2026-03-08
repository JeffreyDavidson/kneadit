<?php

namespace App\Models;

use App\Traits\LogsActivity;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use LogsActivity;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'category_id',
        'is_active',
        'is_featured',
        'image',
        'cost',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cost' => 'decimal:2',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function recipes(): HasMany
    {
        return $this->hasMany(Recipe::class);
    }

    public function recipe(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Recipe::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function seasonalItems(): HasMany
    {
        return $this->hasMany(SeasonalItem::class);
    }

    public function waitlistEntries(): HasMany
    {
        return $this->hasMany(ProductWaitlist::class);
    }

    public function pendingWaitlistCount(): int
    {
        return $this->waitlistEntries()->whereNull('notified_at')->count();
    }

    public function isInSeason(): bool
    {
        $seasonalItems = $this->seasonalItems;
        if ($seasonalItems->isEmpty()) {
            return true; // Not seasonal = always available
        }
        return $seasonalItems->contains(fn ($item) => $item->isCurrentlyAvailable());
    }

    public function getSeasonalBadgeAttribute(): ?string
    {
        $seasonal = $this->seasonalItems->first();
        if (!$seasonal) {
            return null;
        }
        if ($seasonal->isCurrentlyAvailable()) {
            return 'Limited Time';
        }
        return 'Available ' . $seasonal->available_from->format('M') . ' - ' . $seasonal->available_until->format('M');
    }

    public function getMarginAttribute(): ?float
    {
        if ($this->cost && $this->price) {
            return round(($this->price - $this->cost) / $this->price * 100, 2);
        }
        return null;
    }
}
