<?php

use App\Actions\Customers\AnonymiseCustomer;
use App\Models\Customers\CateringInquiry;
use App\Models\Customers\Customer;
use App\Models\Customers\CustomerFavorite;
use App\Models\Customers\CustomerNote;
use App\Models\Customers\CustomerPhoto;
use App\Models\Customers\CustomerProfile;
use App\Models\Customers\CustomerReferral;
use App\Models\Customers\CustomerReminder;
use App\Models\Customers\WaitlistEntry;
use App\Models\Engagement\CustomerCampaignLog;
use App\Models\Engagement\LoyaltyPoint;
use App\Models\Engagement\Review;
use App\Models\Engagement\SurveyResponse;
use App\Models\Inventory\ProductWaitlist;
use App\Models\Operations\ActivityLog;
use App\Models\Orders\Cart;
use App\Models\Orders\CartItem;
use App\Models\Orders\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    Storage::fake('public');
    Date::setTestNow('2026-10-05 12:00');

    test()->customer = Customer::factory()->verified()->withPassword()->withAddress()->create([
        'name' => 'Ada Lovelace',
        'email' => 'ada@example.com',
        'phone' => '555-010-0100',
        'birthday' => '1990-12-10',
        'notes' => 'Allergic to sesame',
        'remember_token' => 'remember-me',
    ]);
    test()->other = Customer::factory()->create(['email' => 'grace@example.com']);
});

function anonymise(Customer $customer): Customer
{
    resolve(AnonymiseCustomer::class)($customer);

    return $customer->fresh();
}

test('keeps the customer row and orders but replaces the personal fields', function () {
    $order = Order::factory()->for(test()->customer)->create();
    CustomerProfile::factory()->for(test()->customer)->create(['birthday' => '1990-12-10', 'notes' => 'Likes rye']);

    $customer = anonymise(test()->customer);

    expect($customer->name)->toBe("Deleted customer #{$customer->id}")
        ->and($customer->email)->toBe("deleted+{$customer->id}@invalid")
        ->and($customer->phone)->toBeNull()
        ->and($customer->address)->toBeNull()
        ->and($customer->city)->toBeNull()
        ->and($customer->state)->toBeNull()
        ->and($customer->zip)->toBeNull()
        ->and($customer->birthday)->toBeNull()
        ->and($customer->notes)->toBeNull()
        ->and($customer->email_verified_at)->toBeNull()
        ->and($customer->marketing_opted_out_at?->toDateTimeString())->toBe('2026-10-05 12:00:00')
        ->and($order->fresh()->customer_id)->toBe($customer->id)
        ->and(CustomerProfile::query()->where('customer_id', $customer->id)->exists())->toBeFalse();
});

test('signs the customer out of every session', function () {
    $before = test()->customer->password;

    $customer = anonymise(test()->customer);

    expect(Customer::query()->whereKey($customer->id)->value('remember_token'))->toBeNull()
        ->and($customer->password)->not->toBeNull()
        ->and($customer->password)->not->toBe($before);
});

test('removes the records keyed on the customer email and leaves other customers alone', function () {
    $email = 'ada@example.com';
    CustomerFavorite::factory()->create(['customer_email' => $email]);
    CustomerCampaignLog::factory()->create(['customer_email' => $email]);
    ProductWaitlist::factory()->create(['customer_email' => $email]);
    WaitlistEntry::factory()->create(['customer_email' => $email]);
    SurveyResponse::factory()->create(['customer_email' => $email]);
    Storage::disk('public')->put('customer-photos/ada.jpg', 'x');
    CustomerPhoto::factory()->create(['customer_email' => $email, 'photo_path' => 'customer-photos/ada.jpg']);
    $cart = Cart::factory()->withEmail($email)->create();
    CartItem::factory()->for($cart)->create();
    $signedInCart = Cart::factory()->create(['customer_id' => test()->customer->id]);

    $kept = [
        CustomerFavorite::factory()->create(['customer_email' => 'grace@example.com']),
        ProductWaitlist::factory()->create(['customer_email' => 'grace@example.com']),
        WaitlistEntry::factory()->create(['customer_email' => 'grace@example.com']),
        SurveyResponse::factory()->create(['customer_email' => 'grace@example.com']),
        CustomerPhoto::factory()->create(['customer_email' => 'grace@example.com']),
        Cart::factory()->withEmail('grace@example.com')->create(),
    ];

    anonymise(test()->customer);

    expect(CustomerFavorite::query()->where('customer_email', $email)->exists())->toBeFalse()
        ->and(CustomerCampaignLog::query()->where('customer_email', $email)->exists())->toBeFalse()
        ->and(ProductWaitlist::query()->where('customer_email', $email)->exists())->toBeFalse()
        ->and(WaitlistEntry::query()->where('customer_email', $email)->exists())->toBeFalse()
        ->and(SurveyResponse::query()->where('customer_email', $email)->exists())->toBeFalse()
        ->and(CustomerPhoto::query()->where('customer_email', $email)->exists())->toBeFalse()
        ->and(Storage::disk('public')->exists('customer-photos/ada.jpg'))->toBeFalse()
        ->and(Cart::query()->whereKey([$cart->id, $signedInCart->id])->exists())->toBeFalse()
        ->and(CartItem::query()->where('cart_id', $cart->id)->exists())->toBeFalse();

    foreach ($kept as $record) {
        expect($record->fresh())->not->toBeNull();
    }
});

test('keeps an approved review without the reviewer and deletes one that is not approved', function () {
    $approved = Review::factory()->approved()->create([
        'customer_email' => 'ada@example.com',
        'customer_name' => 'Ada Lovelace',
        'comment' => 'Lovely sourdough',
    ]);
    $pending = Review::factory()->pending()->create(['customer_email' => 'ada@example.com']);
    $others = Review::factory()->approved()->create(['customer_email' => 'grace@example.com', 'customer_name' => 'Grace']);

    $customer = anonymise(test()->customer);

    expect($approved->fresh()->customer_email)->toBe("deleted+{$customer->id}@invalid")
        ->and($approved->fresh()->customer_name)->toBe("Deleted customer #{$customer->id}")
        ->and($approved->fresh()->comment)->toBe('Lovely sourdough')
        ->and($pending->fresh())->toBeNull()
        ->and($others->fresh()->customer_name)->toBe('Grace');
});

test('anonymises the contact fields and removes the free text of catering inquiries but keeps the inquiry', function () {
    $inquiry = CateringInquiry::factory()->create([
        'customer_name' => 'Ada Lovelace',
        'customer_email' => 'ada@example.com',
        'customer_phone' => '555-010-0100',
        'details' => 'Wedding for Ada and Bob at the lake house',
        'dietary_requirements' => 'Ada is coeliac',
        'venue_address' => '1 Lake Road',
        'notes' => 'Ada prefers morning calls',
    ]);

    $customer = anonymise(test()->customer);

    expect($inquiry->fresh()->details)->toBe('[removed]')
        ->and($inquiry->fresh()->dietary_requirements)->toBeNull()
        ->and($inquiry->fresh()->venue_address)->toBeNull()
        ->and($inquiry->fresh()->notes)->toBeNull()
        ->and($inquiry->fresh()->customer_name)->toBe("Deleted customer #{$customer->id}")
        ->and($inquiry->fresh()->customer_email)->toBe("deleted+{$customer->id}@invalid")
        ->and($inquiry->fresh()->customer_phone)->toBeNull();
});

test('deletes staff notes and reminders about the customer but keeps loyalty points and referrals', function () {
    $notes = CustomerNote::factory()->for(test()->customer)->count(2)->create();
    $reminder = CustomerReminder::factory()->for(test()->customer)->create();
    $points = LoyaltyPoint::factory()->for(test()->customer)->create();
    $referral = CustomerReferral::factory()->create(['referrer_customer_id' => test()->customer->id]);
    $otherNote = CustomerNote::factory()->for(test()->other)->create();

    anonymise(test()->customer);

    expect(CustomerNote::query()->whereKey($notes->modelKeys())->exists())->toBeFalse()
        ->and($reminder->fresh())->toBeNull()
        ->and($points->fresh())->not->toBeNull()
        ->and($referral->fresh())->not->toBeNull()
        ->and($otherNote->fresh())->not->toBeNull();
});

test('blanks a survey response filed against the customer order but not under their email', function () {
    $order = Order::factory()->for(test()->customer)->create();
    $response = SurveyResponse::factory()->create([
        'customer_name' => 'Someone',
        'customer_email' => 'other-address@example.com',
        'answers' => ['feedback' => 'Ada was great'],
        'order_id' => $order->id,
    ]);
    $unrelated = SurveyResponse::factory()->create(['customer_email' => 'grace@example.com', 'answers' => ['a' => 'b']]);

    anonymise(test()->customer);

    expect($response->fresh()->customer_name)->toBeNull()
        ->and($response->fresh()->customer_email)->toBeNull()
        ->and($response->fresh()->answers)->toBeEmpty()
        ->and($unrelated->fresh()->answers)->toBe(['a' => 'b']);
});

test('redacts the customer values in the activity log', function () {
    test()->customer->update(['name' => 'Ada King', 'phone' => '555-020-0200']);

    $customer = anonymise(test()->customer);

    $logs = ActivityLog::query()
        ->where('model_type', Customer::class)
        ->where('model_id', $customer->id)
        ->get();

    expect($logs)->not->toBeEmpty()
        ->and(json_encode($logs->pluck('properties')))->not->toContain('Ada King')
        ->and(json_encode($logs->pluck('properties')))->not->toContain('555-020-0200');
});

test('running it twice changes nothing the second time', function () {
    $customer = anonymise(test()->customer);
    $logCount = ActivityLog::query()->count();
    $updatedAt = $customer->updated_at;

    Date::setTestNow('2026-10-06 12:00');
    $again = anonymise($customer);

    expect($again->updated_at->equalTo($updatedAt))->toBeTrue()
        ->and($again->marketing_opted_out_at?->toDateTimeString())->toBe('2026-10-05 12:00:00')
        ->and(ActivityLog::query()->count())->toBe($logCount);
});
