<?php

use App\Enums\Orders\OrderStatus;
use App\Filament\Widgets\NeedsAttentionWidget;
use App\Models\Orders\Order;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
    Cache::flush();
});

test('needs attention widget renders an item with its icon', function () {
    Order::factory()->create(['status' => OrderStatus::Pending]);

    livewire(NeedsAttentionWidget::class)
        ->assertOk()
        ->assertSee('pending order')
        ->assertSee('Open Orders');
});
