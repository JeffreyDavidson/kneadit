<?php

use App\Mail\Customers\ReviewRequestMail;
use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use Illuminate\Support\Facades\URL;

use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

test('submit review controller passes settings to view', function () {
    $order = Order::factory()->create();
    $signed = URL::temporarySignedRoute('storefront.submitReview', now()->addHour(), [
        'order' => $order->order_number,
    ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->get($signed);

    $response->assertOk()
        ->assertViewHas('settings')
        ->assertViewHas('content')
        ->assertViewHas('ratingDescriptions');
});

test('submit review controller rejects unsigned requests', function () {
    $order = Order::factory()->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('storefront.submitReview', $order->order_number, false));

    $response->assertForbidden();
});

test('submit review controller grants session access so the POST form passes the order.access gate', function () {
    $order = Order::factory()->create();
    $signed = URL::temporarySignedRoute('storefront.submitReview', now()->addHour(), [
        'order' => $order->order_number,
    ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->get($signed);

    $response->assertOk();
    expect(session('verified_order_numbers'))->toContain($order->order_number);
});

test('every star link in the review request email passes the signed middleware and pre-selects its rating', function () {
    $order = Order::factory()->for(Customer::factory())->create();
    preg_match_all('/href="([^"]*rating=\d[^"]*)"/', new ReviewRequestMail($order)->render(), $matches);
    $starUrls = array_map(html_entity_decode(...), $matches[1]);

    expect($starUrls)->toHaveCount(5);

    foreach ($starUrls as $index => $starUrl) {
        $response = withoutMiddleware(tenantMiddleware())
            ->get($starUrl);

        $response->assertOk()
            ->assertViewHas('prefilledRating', $index + 1);
    }
});

test('submit review controller ignores an out-of-range rating', function (string $rating) {
    $order = Order::factory()->create();
    $signed = URL::temporarySignedRoute('storefront.submitReview', now()->addHour(), [
        'order' => $order->order_number,
        'rating' => $rating,
    ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->get($signed);

    $response->assertOk()
        ->assertViewHas('prefilledRating', fn (mixed $prefilledRating): bool => $prefilledRating === null);
})->with(['zero' => '0', 'six' => '6', 'negative' => '-1', 'letter' => 'x', 'script' => 'alert(1)']);
