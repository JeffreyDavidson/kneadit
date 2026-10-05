<?php

use App\Filament\Widgets\InboxWidget;

beforeEach(function () {
    setUpTenantTest();
    test()->widget = new InboxWidget;
});

// InboxWidget methods depend on the initialized bakery (tenant()), which is
// absent in Integration context. The scoped behaviour is covered in
// tests/Feature/Filament/Widgets/InboxWidgetTenantScopeTest.php.

test('get unread count returns zero when no tenant context', function () {
    expect(test()->widget->getUnreadCount())->toBe(0);
});
