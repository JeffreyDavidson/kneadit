<?php

use App\Models\Customers\Customer;
use App\Models\Customers\CustomerFavorite;
use App\Models\Customers\WaitlistEntry;
use App\Models\Engagement\Review;
use App\Models\Operations\ActivityLog;
use App\Models\Orders\Cart;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('deleting a customer removes the records keyed on their email', function () {
    $customer = Customer::factory()->create(['email' => 'ada@example.com']);
    $favorite = CustomerFavorite::factory()->create(['customer_email' => 'ada@example.com']);
    $waitlist = WaitlistEntry::factory()->create(['customer_email' => 'ada@example.com']);
    $cart = Cart::factory()->withEmail('ada@example.com')->create();
    $pending = Review::factory()->pending()->create(['customer_email' => 'ada@example.com']);
    $approved = Review::factory()->approved()->create(['customer_email' => 'ada@example.com', 'customer_name' => 'Ada']);
    $unrelated = CustomerFavorite::factory()->create(['customer_email' => 'grace@example.com']);

    $customer->delete();

    expect($favorite->fresh())->toBeNull()
        ->and($waitlist->fresh())->toBeNull()
        ->and($cart->fresh())->toBeNull()
        ->and($pending->fresh())->toBeNull()
        ->and($approved->fresh()->customer_email)->toBe("deleted+{$customer->id}@invalid")
        ->and($approved->fresh()->customer_name)->toBe("Deleted customer #{$customer->id}")
        ->and($unrelated->fresh())->not->toBeNull();
});

test('deleting a customer redacts their values in the activity log', function () {
    $customer = Customer::factory()->create(['name' => 'Ada']);
    $customer->update(['name' => 'Ada King', 'phone' => '555-020-0200']);

    $customer->delete();

    $properties = json_encode(ActivityLog::query()
        ->where('model_type', Customer::class)
        ->where('model_id', $customer->id)
        ->pluck('properties'));

    expect($properties)->not->toContain('Ada King')
        ->not->toContain('555-020-0200');
});
