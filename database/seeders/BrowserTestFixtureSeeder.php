<?php

namespace Database\Seeders;

use App\Enums\Financial\CouponType;
use App\Enums\Orders\OrderStatus;
use App\Enums\Orders\PaymentStatus;
use App\Enums\Staff\UserRole;
use App\Models\Customers\Customer;
use App\Models\Engagement\Survey;
use App\Models\Financial\Coupon;
use App\Models\Financial\GiftCard;
use App\Models\Inventory\Category;
use App\Models\Inventory\Product;
use App\Models\Operations\BlockedDate;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Models\Staff\User;
use App\Services\Scheduling\BakeryClock;
use App\Services\Settings\SettingsManager;
use Illuminate\Database\Seeder;
use RuntimeException;

// Idempotent seeder that ensures stable fixture records exist in a tenant DB
// so browser tests can reference them by the public constants below.
//
// Not registered in DatabaseSeeder — opt-in only. Run per tenant via:
//   php artisan tenants:seed --tenants=<tenant-id> --class=Database\\Seeders\\BrowserTestFixtureSeeder
//
// Do NOT run in production (the guard below will refuse).
class BrowserTestFixtureSeeder extends Seeder
{
    public const ADMIN_EMAIL = 'browser-test-admin@kneadit.test';

    public const ADMIN_PASSWORD = 'browser-test-password';

    public const RFM_CUSTOMER_EMAIL = 'browser-test-rfm@kneadit.test';

    public const REVIEW_ORDER_NUMBER = 'BROWSER-TEST-REVIEW';

    public const SURVEY_TITLE = 'Browser Test Survey';

    public const DELIVERY_PRODUCT_NAME = 'Browser Test Delivery Loaf';

    public const DELIVERY_PRODUCT_PRICE = 30.00;

    public const DELIVERY_FREE_MINIMUM = 50.00;

    // Two tiers with distinct fees so a test can tell tier 1 from tier 2.
    public const DELIVERY_FEE_TIERS = [
        ['min_distance' => 0, 'max_distance' => 5, 'fee' => 5.00, 'description' => 'Local delivery (0-5 miles)'],
        ['min_distance' => 5, 'max_distance' => 10, 'fee' => 12.00, 'description' => 'Extended delivery (5-10 miles)'],
    ];

    public const COUPON_CODE = 'BROWSERTEST10';

    public const COUPON_PERCENTAGE = 10;

    public const GIFT_CARD_CODE = 'BROW-TEST-GIFT-0050';

    public const GIFT_CARD_BALANCE = 50.00;

    // The storefront shows a blocked date's reason as the closed-day error, and
    // "Closed" is the reason it words as "The bakery is closed on this day."
    public const CLOSED_DAY_REASON = 'Closed';

    // Days from the day the fixture is seeded to the one all-day closure, so
    // re-seeding keeps it inside the 30-day window the storefront loads.
    public const CLOSED_DAY_OFFSET_DAYS = 10;

    public function run(): void
    {
        throw_if(app()->environment('production'), RuntimeException::class, 'BrowserTestFixtureSeeder must never run in production.');

        $this->seedAdminUser();
        $this->skipOnboarding();
        $this->seedReviewableOrder();
        $this->seedRfmChampionCustomer();
        $this->seedActiveSurvey();
        $this->seedDeliveryOrdering();
        $this->seedClosedDay();
        $this->seedDiscounts();
    }

    private function skipOnboarding(): void
    {
        resolve(SettingsManager::class)->set('onboarding_completed_at', now()->toISOString());
    }

    private function seedAdminUser(): void
    {
        User::query()->updateOrCreate(
            ['email' => self::ADMIN_EMAIL],
            [
                'name' => 'Browser Test Admin',
                'password' => self::ADMIN_PASSWORD,
                'role' => UserRole::Owner,
                'email_verified_at' => now(),
            ],
        );
    }

    private function seedReviewableOrder(): void
    {
        $customer = Customer::query()->first() ?? Customer::factory()->create();
        $product = Product::query()->first() ?? Product::factory()->create();

        $order = Order::query()->updateOrCreate(
            ['order_number' => self::REVIEW_ORDER_NUMBER],
            [
                'customer_id' => $customer->id,
                'subtotal' => 25.00,
                'total' => 25.00,
            ],
        );

        if (! $order->orderItems()->exists()) {
            OrderItem::factory()
                ->for($order)
                ->for($product)
                ->create();
        }
    }

    private function seedRfmChampionCustomer(): void
    {
        $customer = Customer::query()->updateOrCreate(
            ['email' => self::RFM_CUSTOMER_EMAIL],
            ['name' => 'Browser Test RFM Champion'],
        );

        foreach (range(1, 4) as $orderNumber) {
            Order::query()->updateOrCreate(
                ['order_number' => "BROWSER-TEST-RFM-{$orderNumber}"],
                [
                    'customer_id' => $customer->id,
                    'status' => OrderStatus::Delivered,
                    'payment_status' => PaymentStatus::Paid,
                    'subtotal' => 125,
                    'total' => 125,
                    'delivery_date' => now()->subDays(5)->toDateString(),
                ],
            );
        }
    }

    private function seedActiveSurvey(): void
    {
        Survey::query()->updateOrCreate(
            ['title' => self::SURVEY_TITLE],
            [
                'description' => 'Fixture survey used by browser tests. Do not delete.',
                'questions' => [
                    ['question' => 'How was your experience?', 'type' => 'rating'],
                    ['question' => 'Any additional feedback?', 'type' => 'text'],
                ],
                'is_active' => true,
            ],
        );
    }

    // Delivery on, two fee tiers, a free-delivery minimum, and one orderable
    // product priced under that minimum, so storefront order tests can cover
    // tier pricing and free delivery.
    private function seedDeliveryOrdering(): void
    {
        resolve(SettingsManager::class)->setMany([
            'delivery_enabled' => '1',
            'delivery_fee_tiers' => json_encode(self::DELIVERY_FEE_TIERS),
            'free_delivery_minimum' => (string) self::DELIVERY_FREE_MINIMUM,
        ]);

        $category = Category::query()->updateOrCreate(
            ['slug' => 'browser-test-delivery'],
            ['name' => 'Browser Test Delivery', 'is_active' => true],
        );

        Product::query()->updateOrCreate(
            ['slug' => 'browser-test-delivery-loaf'],
            [
                'name' => self::DELIVERY_PRODUCT_NAME,
                'price' => self::DELIVERY_PRODUCT_PRICE,
                'category_id' => $category->id,
                'is_active' => true,
            ],
        );
    }

    // One all-day blocked date, so storefront tests can cover a closed day.
    // Re-seeding moves it, so it stays CLOSED_DAY_OFFSET_DAYS ahead of today.
    private function seedClosedDay(): void
    {
        BlockedDate::query()->updateOrCreate(
            ['reason' => self::CLOSED_DAY_REASON],
            [
                'date' => resolve(BakeryClock::class)->today()->addDays(self::CLOSED_DAY_OFFSET_DAYS)->toDateString(),
                'is_all_day' => true,
            ],
        );
    }

    // A percentage coupon and a gift card the storefront order form can apply.
    private function seedDiscounts(): void
    {
        Coupon::query()->updateOrCreate(
            ['code' => self::COUPON_CODE],
            [
                'type' => CouponType::Percentage,
                'percentage' => self::COUPON_PERCENTAGE,
                'starts_at' => now()->subDay(),
                'expires_at' => now()->addYear(),
                'is_active' => true,
            ],
        );

        GiftCard::query()->updateOrCreate(
            ['code' => self::GIFT_CARD_CODE],
            [
                'initial_balance' => self::GIFT_CARD_BALANCE,
                'current_balance' => self::GIFT_CARD_BALANCE,
                'purchaser_name' => 'Browser Test Purchaser',
                'purchaser_email' => 'browser-test-purchaser@kneadit.test',
                'is_active' => true,
                'expires_at' => now()->addYear(),
            ],
        );
    }
}
