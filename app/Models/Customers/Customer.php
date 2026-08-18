<?php

namespace App\Models\Customers;

use App\Builders\Customers\CustomerQueryBuilder;
use App\Casts\PhoneNumberCast;
use App\Models\Engagement\LoyaltyPoint;
use App\Models\Orders\Order;
use App\Notifications\Customers\CustomerPasswordResetNotification;
use App\Notifications\Customers\CustomerVerifyEmailNotification;
use App\Observers\Customers\CustomerObserver;
use App\Observers\LogsActivityObserver;
use Database\Factories\Customers\CustomerFactory;
use Illuminate\Auth\Authenticatable as AuthenticatableTrait;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Auth\Passwords\CanResetPassword as CanResetPasswordTrait;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\UseEloquentBuilder;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\Notifiable;
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
 * @property-read Collection<int, LoyaltyPoint> $loyaltyPoints
 * @property-read int|null $loyalty_points_count
 * @property-read Collection<int, Order> $orders
 * @property-read int|null $orders_count
 * @property-read string $customer_email Populated by ReorderReminders::getCustomers()
 * @property-read string $customer_name Populated by ReorderReminders::getCustomers()
 * @property int $days_since Populated by ReorderReminders::getCustomers()
 * @property-read string|null $last_order_date Populated by Customer::query()->withOrderMetrics()
 * @property-read string|null $last_order_at Populated by LoyaltyAnalytics::getLapsedCustomers()
 * @property-read float|null $orders_sum_total Populated by Customer::query()->withOrderMetrics()
 * @property-read float|null $total_spend
 * @property-read int|null $order_count Populated by withCount('orders as order_count')
 * @property-read int|null $total_orders Populated by ReorderReminders::getCustomers()
 * @property-read float|null $total_spent Populated by ReorderReminders::getCustomers()
 * @property-read int|null $balance Populated by TopLoyaltyCustomersQuery::get()
 * @property Carbon|null $birthday
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer query()
 *
 * @mixin \Eloquent
 */
#[Fillable('name', 'email', 'password', 'phone', 'address', 'city', 'state', 'zip', 'notes', 'birthday', 'referral_code')]
#[Hidden('password', 'remember_token')]
#[ObservedBy([CustomerObserver::class, LogsActivityObserver::class])]
#[UseEloquentBuilder(CustomerQueryBuilder::class)]
#[UseFactory(CustomerFactory::class)]
class Customer extends Model implements Authenticatable, CanResetPassword, MustVerifyEmail
{
    /** @use HasFactory<CustomerFactory> */
    use AuthenticatableTrait, CanResetPasswordTrait, HasFactory, MustVerifyEmailTrait, Notifiable;

    public function sendPasswordResetNotification(mixed $token): void
    {
        $this->notify(new CustomerPasswordResetNotification($token));
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new CustomerVerifyEmailNotification);
    }

    protected function casts(): array
    {
        return [
            'birthday' => 'date',
            'phone' => PhoneNumberCast::class,
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
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
}
