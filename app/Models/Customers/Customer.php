<?php

namespace App\Models\Customers;

use App\Builders\Customers\CustomerQueryBuilder;
use App\Casts\PhoneNumberCast;
use App\Models\Engagement\LoyaltyPoint;
use App\Models\Orders\Order;
use App\Observers\LogsActivityObserver;
use App\ValueObjects\Address;
use Database\Factories\Customers\CustomerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\UseEloquentBuilder;
use Illuminate\Database\Eloquent\Casts\Attribute;
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
 * @property-read string $full_address
 * @property-read Collection<int, LoyaltyPoint> $loyaltyPoints
 * @property-read int|null $loyalty_points_count
 * @property-read Collection<int, Order> $orders
 * @property-read int|null $orders_count
 * @property-read string|null $last_order_date Populated by CustomerIntelligence::enrichQuery()
 * @property-read float|null $orders_sum_total Populated by CustomerIntelligence::enrichQuery()
 * @property-read float|null $total_spend
 * @property Carbon|null $birthday
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer query()
 *
 * @mixin \Eloquent
 */
#[Fillable('name', 'email', 'phone', 'address', 'city', 'state', 'zip', 'notes', 'birthday')]
#[ObservedBy(LogsActivityObserver::class)]
#[UseEloquentBuilder(CustomerQueryBuilder::class)]
class Customer extends Model
{
    /** @use HasFactory<CustomerFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'birthday' => 'date',
            'phone' => PhoneNumberCast::class,
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

    /** @return Attribute<Address, never> */
    protected function addressObject(): Attribute
    {
        return Attribute::make(
            get: fn () => new Address(
                street: $this->address,
                city: $this->city,
                state: $this->state,
                zip: $this->zip,
            ),
        );
    }

    /** @return Attribute<string, never> */
    protected function fullAddress(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->address_object->formatted(),
        );
    }

    protected static function newFactory(): CustomerFactory
    {
        return CustomerFactory::new();
    }
}
