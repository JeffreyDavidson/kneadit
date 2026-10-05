<?php

use App\Mail\Customers\AbandonedCartRecoveryMail;
use App\Models\Customers\Customer;
use App\Models\Financial\Coupon;
use App\Models\Orders\Cart;
use App\Models\Orders\CartItem;
use App\Models\Orders\Order;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Mail;

use function Pest\Laravel\artisan;

beforeEach(fn () => setUpCentralTest());

test('carts:send-abandonment-emails runs successfully with no tenants', function () {
    Mail::fake();

    $this->artisan('carts:send-abandonment-emails')->assertSuccessful();

    Mail::assertNothingQueued();
});

test('command source uses TenancyManager + abandonedCartRecoveryEnabled', function () {
    $source = file_get_contents(app_path('Console/Commands/Customers/SendAbandonedCartRecoveryCommand.php'));

    expect($source)
        ->toContain('TenancyManager')
        ->toContain('forEachTenant')
        ->toContain('abandonedCartRecoveryEnabled')
        ->toContain('recovery_sent_at')
        ->toContain('recovery_claimed_at')
        ->toContain('converted_at');
});

test('command source atomically claims carts before queueing recovery mail', function () {
    $source = file_get_contents(app_path('Console/Commands/Customers/SendAbandonedCartRecoveryCommand.php'));

    expect($source)
        ->toContain("whereNull('recovery_claimed_at')")
        ->toContain("update(['recovery_claimed_at' => now()])")
        ->toContain('now()->subHour()');
});

describe('a customer who has already ordered', function () {
    beforeEach(function () {
        runCommandsAsOneTenant();
        Mail::fake();
        Date::setTestNow('2026-10-05 12:00');
        settings([
            'abandoned_cart_recovery_enabled' => '1',
            'abandoned_cart_recovery_hours' => '24',
            'abandoned_cart_recovery_coupon_dollars' => '5',
        ]);
    });

    test('is not emailed or given a coupon, and the cart is marked converted', function () {
        $customer = Customer::factory()->create(['email' => 'buyer@example.com']);
        $cart = Cart::factory()->withEmail('Buyer@Example.com')->create(['last_activity_at' => '2026-10-03 12:00']);
        CartItem::factory()->for($cart)->create();
        Order::factory()->for($customer)->create(['created_at' => '2026-10-04 12:00']);

        artisan('carts:send-abandonment-emails')->assertSuccessful();

        Mail::assertNotQueued(AbandonedCartRecoveryMail::class);
        expect(Coupon::query()->count())->toBe(0)
            ->and($cart->fresh()->converted_at)->not->toBeNull();
    });

    test('is still emailed when the order was placed before the cart was last touched', function () {
        $customer = Customer::factory()->create(['email' => 'buyer@example.com']);
        $cart = Cart::factory()->withEmail('buyer@example.com')->create(['last_activity_at' => '2026-10-03 12:00']);
        CartItem::factory()->for($cart)->create();
        Order::factory()->for($customer)->create(['created_at' => '2026-10-01 12:00']);

        artisan('carts:send-abandonment-emails')->assertSuccessful();

        Mail::assertQueued(AbandonedCartRecoveryMail::class, fn (AbandonedCartRecoveryMail $mail): bool => $mail->hasTo('buyer@example.com'));
        expect(Coupon::query()->count())->toBe(1)
            ->and($cart->fresh()->converted_at)->toBeNull();
    });
});
