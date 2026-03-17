<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    use HasFactory;
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

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * @return HasMany<Recipe, $this>
     */
    public function recipes(): HasMany
    {
        return $this->hasMany(Recipe::class);
    }

    /**
     * @return HasOne<Recipe, $this>
     */
    public function recipe(): HasOne
    {
        return $this->hasOne(Recipe::class);
    }

    /**
     * @return HasMany<Review, $this>
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * @return HasMany<SeasonalItem, $this>
     */
    public function seasonalItems(): HasMany
    {
        return $this->hasMany(SeasonalItem::class);
    }

    /**
     * @return HasMany<CustomerPhoto, $this>
     */
    public function customerPhotos(): HasMany
    {
        return $this->hasMany(CustomerPhoto::class);
    }

    /**
     * @return HasMany<PageView, $this>
     */
    public function pageViews(): HasMany
    {
        return $this->hasMany(PageView::class);
    }

    /**
     * @return HasMany<SocialPost, $this>
     */
    public function socialPosts(): HasMany
    {
        return $this->hasMany(SocialPost::class);
    }

    /**
     * @return HasMany<ProductWaitlist, $this>
     */
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

        return $seasonalItems->contains(fn (SeasonalItem $item) => $item->isCurrentlyAvailable());
    }

    protected function getSeasonalBadgeAttribute(): ?string
    {
        $seasonal = $this->seasonalItems->first();
        if (! $seasonal) {
            return null;
        }
        if ($seasonal->isCurrentlyAvailable()) {
            return 'Limited Time';
        }

        return 'Available '.$seasonal->available_from->format('M').' - '.$seasonal->available_until->format('M');
    }

    protected function getMarginAttribute(): ?float
    {
        if ($this->cost && $this->price) {
            return round(($this->price - $this->cost) / $this->price * 100, 2);
        }

        return null;
    }

    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true);
    }

    #[Scope]
    protected function featured(Builder $query): void
    {
        $query->where('is_featured', true);
    }
}
