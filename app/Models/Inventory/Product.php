<?php

namespace App\Models\Inventory;

use App\Builders\Inventory\ProductQueryBuilder;
use App\Models\Concerns\LogsActivity;
use App\Models\Content\SocialPost;
use App\Models\Customers\CustomerPhoto;
use App\Models\Engagement\PageView;
use App\Models\Engagement\Review;
use App\Models\Orders\OrderItem;
use App\Presenters\ProductPresenter;
use App\Support\ProfitMargin;
use Database\Factories\Inventory\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseEloquentBuilder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * @property-read Category|null $category
 * @property-read Collection<int, CustomerPhoto> $customerPhotos
 * @property-read int|null $customer_photos_count
 * @property-read float|null $margin
 * @property-read string|null $seasonal_badge
 * @property-read Collection<int, ProductImage> $images
 * @property-read int|null $images_count
 * @property-read Collection<int, OrderItem> $orderItems
 * @property-read int|null $order_items_count
 * @property-read Collection<int, PageView> $pageViews
 * @property-read int|null $page_views_count
 * @property-read ProductImage|null $primaryImage
 * @property-read Recipe|null $recipe
 * @property-read Collection<int, Recipe> $recipes
 * @property-read int|null $recipes_count
 * @property-read Collection<int, Review> $reviews
 * @property-read int|null $reviews_count
 * @property-read float|null $reviews_avg_rating
 * @property-read Collection<int, SeasonalItem> $seasonalItems
 * @property-read int|null $seasonal_items_count
 * @property-read Collection<int, SocialPost> $socialPosts
 * @property-read int|null $social_posts_count
 * @property-read Collection<int, ProductWaitlist> $waitlistEntries
 * @property-read int|null $waitlist_entries_count
 *
 * @method static \Database\Factories\ProductFactory factory($count = null, $state = [])
 *
 * @property Carbon|null $available_from
 * @property Carbon|null $available_until
 *
 * @mixin \Eloquent
 */
#[Fillable('name', 'slug', 'description', 'price', 'category_id', 'is_active', 'is_featured', 'image', 'cost')]
#[UseEloquentBuilder(ProductQueryBuilder::class)]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    use LogsActivity;

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'cost' => 'decimal:2',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return HasMany<ProductImage, $this>
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    /**
     * @return HasOne<ProductImage, $this>
     */
    public function primaryImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    /** @return Attribute<string|null, never> */
    protected function primaryImageUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                $this->loadMissing('primaryImage');

                if ($primary = $this->primaryImage) {
                    return Storage::disk('public')->url($primary->path);
                }

                if ($this->image) {
                    return Storage::disk('public')->url($this->image);
                }

                return null;
            },
        );
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

    /** @return Attribute<bool, never> */
    protected function isInSeason(): Attribute
    {
        return Attribute::make(
            get: function () {
                $this->loadMissing('seasonalItems');

                if ($this->seasonalItems->isEmpty()) {
                    return true;
                }

                return $this->seasonalItems->contains(fn (SeasonalItem $item) => $item->is_currently_available);
            },
        );
    }

    /** @return Attribute<string|null, never> */
    protected function seasonalBadge(): Attribute
    {
        return Attribute::make(
            get: fn () => (new ProductPresenter($this))->seasonalBadge(),
        );
    }

    /** @return Attribute<float|null, never> */
    protected function margin(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->cost && $this->price
                ? ProfitMargin::calculate((float) $this->price, (float) $this->cost)
                : null,
        );
    }

    protected static function newFactory(): ProductFactory
    {
        return ProductFactory::new();
    }
}
