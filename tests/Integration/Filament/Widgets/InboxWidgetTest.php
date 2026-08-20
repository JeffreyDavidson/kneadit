<?php

use App\Filament\Widgets\InboxWidget;

beforeEach(function () {
    setUpTenantTest();
    test()->widget = new InboxWidget;
});

// InboxWidget methods depend on Filament::getTenant() which returns null
// in Integration context. We test what we can without that context.

test('get unread count returns zero when no tenant context', function () {
    expect(testFixture('widget', InboxWidget::class)->getUnreadCount())->toBe(0);
});
