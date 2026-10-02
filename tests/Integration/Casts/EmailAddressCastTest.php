<?php

use App\Models\Customers\CateringInquiry;
use App\Models\Customers\Customer;
use App\Models\Customers\CustomerFavorite;
use App\Models\Customers\CustomerPhoto;
use App\Models\Customers\WaitlistEntry;
use App\Models\Engagement\CustomerCampaignLog;
use App\Models\Engagement\Review;
use App\Models\Engagement\SurveyResponse;
use App\Models\Inventory\ProductWaitlist;
use App\Models\Orders\Cart;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

dataset('customer email columns', [
    'customers.email' => [Customer::class, 'email'],
    'carts.customer_email' => [Cart::class, 'customer_email'],
    'customer_favorites.customer_email' => [CustomerFavorite::class, 'customer_email'],
    'waitlist_entries.customer_email' => [WaitlistEntry::class, 'customer_email'],
    'product_waitlists.customer_email' => [ProductWaitlist::class, 'customer_email'],
    'customer_campaign_logs.customer_email' => [CustomerCampaignLog::class, 'customer_email'],
    'customer_photos.customer_email' => [CustomerPhoto::class, 'customer_email'],
    'reviews.customer_email' => [Review::class, 'customer_email'],
    'survey_responses.customer_email' => [SurveyResponse::class, 'customer_email'],
    'catering_inquiries.customer_email' => [CateringInquiry::class, 'customer_email'],
]);

test('stores customer emails lowercased and trimmed', function (string $model, string $column) {
    $record = $model::factory()->create([$column => '  Mixed.Case@Example.COM ']);

    expect($record->{$column})->toBe('mixed.case@example.com')
        ->and($model::query()->whereKey($record->getKey())->value($column))->toBe('mixed.case@example.com');
})->with('customer email columns');

test('keeps a null email null', function () {
    $cart = Cart::factory()->create(['customer_email' => null]);

    expect($cart->fresh()->customer_email)->toBeNull();
});
