<?php

use App\Filament\Widgets\InboxWidget;
use App\Models\Platform\PlatformMessage;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use Illuminate\Support\Facades\DB;
use Stancl\Tenancy\Contracts\Tenant as TenantContract;

beforeEach(function () {
    setUpCentralTest();
    test()->actingAs(User::factory()->owner()->create());

    foreach (['bakery-a', 'bakery-b'] as $id) {
        DB::table('tenants')->insert([
            'id' => $id,
            'name' => $id,
            'email' => "{$id}@test.com",
            'plan' => 'pro',
            'store_name' => $id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    app()->instance(TenantContract::class, Tenant::query()->find('bakery-a'));
});

test('the inbox widget is hidden when only another bakery has unread messages', function () {
    PlatformMessage::factory()->create(['tenant_id' => 'bakery-b']);

    expect(InboxWidget::canView())->toBeFalse();
});

test('the inbox widget shows when the current bakery has unread messages', function () {
    PlatformMessage::factory()->create(['tenant_id' => 'bakery-a']);

    expect(InboxWidget::canView())->toBeTrue();
});

test('the inbox widget counts only the current bakery unread messages', function () {
    PlatformMessage::factory()->count(2)->create(['tenant_id' => 'bakery-a']);
    PlatformMessage::factory()->count(3)->create(['tenant_id' => 'bakery-b']);

    expect((new InboxWidget)->getUnreadCount())->toBe(2);
});
