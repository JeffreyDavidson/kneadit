<?php

use App\Models\Customers\CateringInquiry;
use App\Models\Customers\ContactMessage;
use App\Models\Inventory\Ingredient;
use App\Models\Orders\Order;
use App\Queries\Dashboard\NeedsAttentionQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('returns operational attention counts', function () {
    Order::factory()->pending()->count(2)->create();
    Order::factory()->confirmed()->create();
    ContactMessage::factory()->unread()->count(3)->create();
    ContactMessage::factory()->read()->create();
    CateringInquiry::factory()->inquiry()->create();
    CateringInquiry::factory()->quoted()->create();
    Ingredient::factory()->lowStock()->count(2)->create();
    Ingredient::factory()->create(['current_stock' => 10, 'low_stock_threshold' => 5]);

    $counts = resolve(NeedsAttentionQuery::class)->get();

    expect($counts->pendingOrders)->toBe(2)
        ->and($counts->unreadMessages)->toBe(3)
        ->and($counts->newInquiries)->toBe(1)
        ->and($counts->lowStockIngredients)->toBe(2)
        ->and($counts->hasItems())->toBeTrue();
});
