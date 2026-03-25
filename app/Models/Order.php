<?php

namespace App\Models;

use App\Enums\DeliveryType;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Traits\LogsActivity;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property OrderStatus $status
 * @property PaymentStatus $payment_status
 * @property PaymentMethod $payment_method
 * @property DeliveryType $delivery_type
 * @property-read Coupon|null $coupon
 * @property-read Customer|null $customer
 * @property-read Collection<int, GiftCardTransaction> $giftCardTransactions
 * @property-read int|null $gift_card_transactions_count
 * @property-read Collection<int, LoyaltyPoint> $loyaltyPoints
 * @property-read int|null $loyalty_points_count
 * @property-read Collection<int, OrderMessage> $messages
 * @property-read int|null $messages_count
 * @property-read Collection<int, OrderItem> $orderItems
 * @property-read int|null $order_items_count
 * @property-read Collection<int, Review> $reviews
 * @property-read int|null $reviews_count
 * @property-read Collection<int, SurveyResponse> $surveyResponses
 * @property-read int|null $survey_responses_count
 * @property-read User|null $user
 *
 * @method static Builder<static>|Order active()
 * @method static Builder<static>|Order byStatus(\App\Enums\OrderStatus $status)
 * @method static \Database\Factories\OrderFactory factory($count = null, $state = [])
 * @method static Builder<static>|Order newModelQuery()
 * @method static Builder<static>|Order newQuery()
 * @method static Builder<static>|Order paid()
 * @method static Builder<static>|Order query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order paid()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order byStatus(\App\Enums\OrderStatus $status)
 *
 * @property-read string|null $last_order_date
 * @property int|null $days_since
 * @property-read float|null $revenue
 * @property-read string|null $date
 * @property Carbon|null $delivery_date
 * @property Carbon|null $delivery_time
 *
 * @mixin \Eloquent
 */
class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    use LogsActivity;

    public function getRouteKeyName(): string
    {
        return 'order_number';
    }

    protected $fillable = [
        'order_number',
        'customer_id',
        'status',
        'payment_status',
        'payment_method',
        'subtotal',
        'delivery_fee',
        'discount_amount',
        'total',
        'paypal_invoice_id',
        'delivery_address',
        'delivery_type',
        'delivery_date',
        'delivery_time',
        'notes',
        'user_id',
        'coupon_id',
        'review_request_sent_at',
        'stripe_checkout_session_id',
        'stripe_payment_intent_id',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'delivery_date' => 'date',
            'delivery_time' => 'datetime:H:i',
            'review_request_sent_at' => 'datetime',
            'status' => OrderStatus::class,
            'payment_status' => PaymentStatus::class,
            'payment_method' => PaymentMethod::class,
            'delivery_type' => DeliveryType::class,
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Order $order) {
            if (! $order->order_number) {
                $order->order_number = 'ORD-'.str_pad((string) (static::query()->count() + 1), 6, '0', STR_PAD_LEFT);
            }
        });
    }

    /**
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Coupon, $this>
     */
    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * @return HasMany<OrderMessage, $this>
     */
    public function messages(): HasMany
    {
        return $this->hasMany(OrderMessage::class);
    }

    /**
     * @return HasMany<LoyaltyPoint, $this>
     */
    public function loyaltyPoints(): HasMany
    {
        return $this->hasMany(LoyaltyPoint::class);
    }

    /**
     * @return HasMany<Review, $this>
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * @return HasMany<SurveyResponse, $this>
     */
    public function surveyResponses(): HasMany
    {
        return $this->hasMany(SurveyResponse::class);
    }

    /**
     * @return HasMany<GiftCardTransaction, $this>
     */
    public function giftCardTransactions(): HasMany
    {
        return $this->hasMany(GiftCardTransaction::class);
    }

    /** @param Builder<Order> $query */
    #[Scope]
    protected function paid(Builder $query): void
    {
        $query->where('payment_status', PaymentStatus::Paid);
    }

    /** @param Builder<Order> $query */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->whereNotIn('status', [OrderStatus::Cancelled]);
    }

    /** @param Builder<Order> $query */
    #[Scope]
    protected function byStatus(Builder $query, OrderStatus $status): void
    {
        $query->where('status', $status);
    }
}
