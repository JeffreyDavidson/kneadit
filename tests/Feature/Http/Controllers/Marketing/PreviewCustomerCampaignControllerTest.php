<?php

declare(strict_types=1);

use App\Models\Engagement\CustomerCampaign;
use App\Models\Staff\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\withoutMiddleware;

uses(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('renders the campaign mailable as HTML for a manager', function () {
    $manager = User::factory()->manager()->create();
    $campaign = CustomerCampaign::factory()->create([
        'subject' => 'Spring Sale',
        'body' => 'Fresh croissants this weekend.',
    ]);

    $response = withoutMiddleware(tenantMiddleware())
        ->actingAs($manager)
        ->get(route('campaign.preview', ['campaign' => $campaign]));

    $response->assertOk();
    $response->assertSee('Fresh croissants this weekend.', escape: false);
});

test('redirects guests to login', function () {
    $campaign = CustomerCampaign::factory()->create();

    withoutMiddleware(tenantMiddleware())
        ->get(route('campaign.preview', ['campaign' => $campaign]))
        ->assertRedirect(route('login'));
});

test('forbids non-manager staff', function () {
    $staff = User::factory()->staff()->create();
    $campaign = CustomerCampaign::factory()->create();

    withoutMiddleware(tenantMiddleware())
        ->actingAs($staff)
        ->get(route('campaign.preview', ['campaign' => $campaign]))
        ->assertForbidden();
});
