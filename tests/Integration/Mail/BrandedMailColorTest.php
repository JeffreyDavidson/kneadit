<?php

use App\Mail\Platform\StaffInvitationMail;
use App\Models\Platform\Tenant;
use App\Models\Staff\StaffInvitation;

beforeEach(function () {
    setUpCentralTest();

    // Persist the central tenant without provisioning a separate database for each test.
    test()->tenant = Tenant::withoutEvents(fn (): Tenant => Tenant::factory()->create());

    tenancy()->getBootstrappersUsing = fn (): array => [];
    tenancy()->initialize(test()->tenant);
});

function renderBrandedMail(): string
{
    $invitation = StaffInvitation::factory()->create();

    return new StaffInvitationMail($invitation, 'Test Bakery', 'https://example.test/accept')->render();
}

test('branded mail prints the default colors when the stored brand colors are not hex colors', function () {
    test()->tenant->update([
        'brand_color_primary' => 'red;}body{display:none',
        'brand_color_secondary' => 'blue',
    ]);

    $html = renderBrandedMail();

    expect($html)->toContain('background-color: #d4920c;')
        ->and($html)->not->toContain('display:none')
        ->and($html)->not->toContain('blue');
});

test('branded mail prints valid hex brand colors', function () {
    test()->tenant->update(['brand_color_primary' => '#336699']);

    expect(renderBrandedMail())->toContain('background-color: #336699;');
});
