<?php

use App\Models\Customers\Customer;
use App\Models\Engagement\LoyaltyPoint;
use App\Models\Engagement\LoyaltyReward;
use App\Presenters\LoyaltyRewardPresenter;
use App\Services\Customers\CustomerIntelligence;
use Illuminate\Support\Facades\Route;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\withoutMiddleware;

beforeEach(function () {
    setUpTenantTest();
});

test('loyalty controller show passes vm to view', function () {
    $response = test()
        ->withoutMiddleware(tenantMiddleware())
        ->get(route('storefront.rewards', [], false));

    $response->assertOk()
        ->assertViewHas('vm');
});

test('loyalty point model exists', function () {
    expect(class_exists(LoyaltyPoint::class))->toBeTrue();
});

test('loyalty reward model exists', function () {
    expect(class_exists(LoyaltyReward::class))->toBeTrue();
});

test('customer total points calculation', function () {
    $customer = Customer::factory()->create();

    LoyaltyPoint::factory()->for($customer)->earned(100)->create(['description' => 'Order #1']);
    LoyaltyPoint::factory()->for($customer)->earned(50)->create(['description' => 'Order #2']);
    LoyaltyPoint::factory()->for($customer)->redeemed(30)->create(['description' => 'Reward redeemed']);

    expect(resolve(CustomerIntelligence::class)->metrics($customer)->totalPoints)->toBe(120);
});

test('loyalty reward can be created', function () {
    $reward = LoyaltyReward::factory()->freeProduct()->create([
        'name' => 'Free Cookie',
        'description' => 'Get a free cookie!',
        'points_required' => 100,
    ]);

    test()->assertDatabaseHas('loyalty_rewards', ['name' => 'Free Cookie']);
    expect($reward->is_active)->toBeTrue();
});

test('loyalty reward type label percentage', function () {
    $reward = LoyaltyReward::factory()->percentageDiscount()->create([
        'name' => '10% Off',
        'points_required' => 50,
        'discount_percentage' => 10,
    ]);

    expect(LoyaltyRewardPresenter::for($reward)->rewardTypeLabel())->toContain('% Off');
});

test('loyalty reward type label fixed', function () {
    $reward = LoyaltyReward::factory()->fixedDiscount()->create([
        'name' => '$5 Off',
        'points_required' => 75,
        'discount_amount' => 5.00,
    ]);

    expect(LoyaltyRewardPresenter::for($reward)->rewardTypeLabel())->toBe('$5.00 Off');
});

test('customer lifetime points earned', function () {
    $customer = Customer::factory()->create();

    LoyaltyPoint::factory()->for($customer)->earned(200)->create(['description' => 'Big order']);
    LoyaltyPoint::factory()->for($customer)->redeemed(50)->create(['description' => 'Reward']);

    expect(resolve(CustomerIntelligence::class)->metrics($customer)->lifetimePointsEarned)->toBe(200);
});

test('loyalty points belong to customer', function () {
    $customer = Customer::factory()->create();

    $point = LoyaltyPoint::factory()->for($customer)->earned(100)->create(['description' => 'Order']);

    expect($point->customer->id)->toBe($customer->id);
});

test('rewards history shows the signed points for each entry type', function (callable $seed, string $expected, string $unexpected) {
    $customer = Customer::factory()->verified()->withPassword()->create();
    $seed($customer);

    actingAs($customer, 'customer');

    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('storefront.rewards', [], false));

    $text = preg_replace('/\s+/', '', strip_tags($response->getContent()));
    $response->assertOk();
    expect($text)
        ->toContain($expected)
        ->not->toContain($unexpected);
})->with([
    'earned' => [fn (Customer $customer) => LoyaltyPoint::factory()->earned(75)->for($customer)->create(), '+75', '-75'],
    'redeemed' => [fn (Customer $customer) => LoyaltyPoint::factory()->redeemed(100)->for($customer)->create(), '-100', '+100'],
    'negative adjustment' => [fn (Customer $customer) => LoyaltyPoint::factory()->adjusted(-25)->for($customer)->create(), '-25', '+-25'],
    'positive adjustment' => [fn (Customer $customer) => LoyaltyPoint::factory()->adjusted(40)->for($customer)->create(), '+40', '+-40'],
]);

test('a signed-out visitor sees the program but no points and no email lookup', function () {
    $other = Customer::factory()->verified()->withPassword()->create(['name' => 'Priya Patel']);
    LoyaltyPoint::factory()->earned(4321)->for($other)->create(['description' => 'Secret order']);
    LoyaltyReward::factory()->freeProduct()->create(['name' => 'Free Cookie', 'points_required' => 100]);

    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('storefront.rewards', [], false));

    $response->assertOk()
        ->assertSee('Free Cookie')
        ->assertSee('Sign in to see your points')
        ->assertSeeHtml(route('account.login.show'))
        ->assertDontSee('Priya Patel')
        ->assertDontSee('4,321')
        ->assertDontSee('Secret order')
        ->assertDontSee('loyalty-lookup-form')
        ->assertDontSee('Check Balance');
});

test('the points lookup by email no longer exists', function () {
    $customer = Customer::factory()->verified()->withPassword()->create();
    LoyaltyPoint::factory()->earned(150)->for($customer)->create();

    expect(Route::has('rewards.check'))->toBeFalse();

    withoutMiddleware(tenantMiddleware())
        ->post('/rewards/check', ['email' => $customer->email])
        ->assertNotFound();
});

test('a signed-in verified customer sees only their own points', function () {
    $customer = Customer::factory()->verified()->withPassword()->create(['name' => 'Alice Baker']);
    LoyaltyPoint::factory()->earned(150)->for($customer)->create(['description' => 'Alice order']);
    $other = Customer::factory()->verified()->withPassword()->create(['name' => 'Priya Patel']);
    LoyaltyPoint::factory()->earned(4321)->for($other)->create(['description' => 'Priya order']);

    actingAs($customer, 'customer');

    withoutMiddleware(tenantMiddleware())
        ->get(route('storefront.rewards', [], false))
        ->assertOk()
        ->assertSee('Alice Baker')
        ->assertSee('Alice order')
        ->assertSee('150')
        ->assertDontSee('Priya Patel')
        ->assertDontSee('Priya order')
        ->assertDontSee('4,321')
        ->assertDontSee('Sign in to see your points')
        ->assertDontSee('loyalty-lookup-form');
});

test('a signed-in customer with an unverified email is sent to verify', function () {
    $customer = Customer::factory()->unverified()->withPassword()->create();
    LoyaltyPoint::factory()->earned(150)->for($customer)->create();

    actingAs($customer, 'customer');

    withoutMiddleware(tenantMiddleware())
        ->get(route('storefront.rewards', [], false))
        ->assertRedirect(route('account.email.verify.notice'));
});

test('signing in from the rewards page returns the customer to the rewards page', function () {
    $customer = Customer::factory()->verified()->create([
        'email' => 'alice@example.com',
        'password' => 'password123',
    ]);

    withoutMiddleware(tenantMiddleware())
        ->get(route('storefront.rewards', [], false))
        ->assertOk();

    withoutMiddleware(tenantMiddleware())
        ->post(route('account.login', [], false), [
            'email' => $customer->email,
            'password' => 'password123',
        ])
        ->assertRedirect(route('storefront.rewards'));
});
