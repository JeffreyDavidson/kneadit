<?php

namespace Database\Seeders;

use App\Enums\Orders\OrderStatus;
use App\Enums\Orders\PaymentStatus;
use App\Enums\Staff\UserRole;
use App\Models\Customers\Customer;
use App\Models\Engagement\Survey;
use App\Models\Inventory\Product;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Models\Staff\User;
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

    public function run(): void
    {
        throw_if(app()->environment('production'), RuntimeException::class, 'BrowserTestFixtureSeeder must never run in production.');

        $this->seedAdminUser();
        $this->skipOnboarding();
        $this->seedReviewableOrder();
        $this->seedRfmChampionCustomer();
        $this->seedActiveSurvey();
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
}
