<?php

namespace App\Models\Orders;

use App\Builders\Orders\OrderQueryBuilder;
use App\Enums\Orders\DeliveryType;
use App\Enums\Orders\OrderStatus;
use App\Enums\Orders\PaymentMethod;
use App\Enums\Orders\PaymentStatus;
use App\Models\Customers\Customer;
use App\Models\Engagement\LoyaltyPoint;
use App\Models\Engagement\Review;
use App\Models\Engagement\SurveyResponse;
use App\Models\Financial\Coupon;
use App\Models\Financial\CouponTransaction;
use App\Models\Financial\GiftCard;
use App\Models\Financial\GiftCardTransaction;
use App\Models\Staff\User;
use App\Observers\LogsActivityObserver;
use App\Observers\Orders\OrderObserver;
use Database\Factories\Orders\OrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\UseEloquentBuilder;
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
 * @property-read Collection<int, CouponTransaction> $couponTransactions
 * @property-read int|null $coupon_transactions_count
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
 * @method static \Database\Factories\OrderFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order paid()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Order byStatus(\App\Enums\Orders\OrderStatus $status)
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
#[Fillable('order_number', 'customer_id', 'status', 'payment_status', 'payment_method', 'subtotal', 'delivery_fee', 'discount_amount', 'total', 'paypal_invoice_id', 'delivery_address', 'delivery_type', 'delivery_date', 'delivery_time', 'notes', 'user_id', 'coupon_id', 'gift_card_id', 'gift_card_amount', 'review_request_sent_at', 'stripe_checkout_session_id', 'stripe_payment_intent_id')]
#[ObservedBy([OrderObserver::class, LogsActivityObserver::class])]
#[UseEloquentBuilder(OrderQueryBuilder::class)]
class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    protected $attributes = [
        'status' => OrderStatus::Pending,
        'payment_status' => PaymentStatus::Unpaid,
        'payment_method' => PaymentMethod::Cash,
        'delivery_type' => DeliveryType::Pickup,
        'delivery_fee' => 0,
        'discount_amount' => 0,
        'gift_card_amount' => 0,
    ];

    public function getRouteKeyName(): string
    {
        return 'order_number';
    }

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'gift_card_amount' => 'decimal:2',
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
     * @return BelongsTo<GiftCard, $this>
     */
    public function giftCard(): BelongsTo
    {
        return $this->belongsTo(GiftCard::class);
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
     * @return HasMany<CouponTransaction, $this>
     */
    public function couponTransactions(): HasMany
    {
        return $this->hasMany(CouponTransaction::class);
    }

    /**
     * @return HasMany<GiftCardTransaction, $this>
     */
    public function giftCardTransactions(): HasMany
    {
        return $this->hasMany(GiftCardTransaction::class);
    }

    protected static function newFactory(): OrderFactory
    {
        return OrderFactory::new();
    }
}
