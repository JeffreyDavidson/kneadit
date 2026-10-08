<?php

use App\DataTransferObjects\Settings\StoreInfo;
use App\Filament\Pages\Platform\Onboarding;
use App\Http\Middleware\EnsureOnboardingComplete;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

use function Pest\Laravel\withoutMiddleware;
use function Pest\Livewire\livewire;

beforeEach(function () {
    setUpCentralTest();
    test()->actingAs(User::factory()->owner()->create());

    // Persist the central tenant without provisioning a separate database for each test.
    $tenant = Tenant::withoutEvents(fn (): Tenant => Tenant::factory()->create());
    tenancy()->getBootstrappersUsing = fn (): array => [];
    tenancy()->initialize($tenant);
});

test('onboarding page renders for a bakery that has not finished onboarding', function () {
    livewire(Onboarding::class)
        ->assertSuccessful()
        ->assertSee('Set up your bakery');
});

test('onboarding page is a focused page without the admin sidebar or topbar', function () {
    settings(['store_name' => 'Bakery on Biscotto']);

    $response = withoutMiddleware(PreventAccessFromCentralDomains::class)
        ->get(Onboarding::getUrl());

    $response->assertSuccessful()
        ->assertSee('Bakery on Biscotto')
        ->assertSee('Save and finish later')
        ->assertDontSeeHtml('class="fi-sidebar-nav"')
        ->assertDontSeeHtml('class="fi-topbar-ctn"');
});

test('onboarding page lists every setup step', function () {
    livewire(Onboarding::class)
        ->assertSeeInOrder([
            'Welcome',
            'Contact info',
            'Branding',
            'First product',
            'Business hours',
            'Compliance',
            'Delivery',
            'Payments',
            'Preview',
            'Complete',
        ]);
});

test('onboarding page offers to save and finish later by signing out', function () {
    livewire(Onboarding::class)
        ->assertSee('Save and finish later')
        ->assertSeeHtml('action="'.filament()->getLogoutUrl().'"');
});

test('onboarding page shows the step count and greets the bakery by name', function () {
    settings(['store_name' => 'Bakery on Biscotto']);

    livewire(Onboarding::class)
        ->assertSee('Step 1 of 10')
        ->assertSee("Let's get Bakery on Biscotto ready")
        ->assertSee('about 5 minutes')
        ->assertDontSee('Welcome to KneadIt')
        ->assertDontSeeHtml('kn-wizard-progress');
});

test('onboarding page has one main heading', function () {
    $html = livewire(Onboarding::class)->html();

    expect(substr_count($html, '<h1'))->toBe(1);
});

test('completing onboarding still records the completion time', function () {
    livewire(Onboarding::class)->call('completeOnboarding');

    expect(settings('onboarding_completed_at'))->not->toBeNull();
});

test('onboarding page renders the stripe status when stripe is a payment method', function (array $stripeSettings, string $expected) {
    settings([
        'payment_methods' => json_encode(['stripe']),
        ...$stripeSettings,
    ]);

    livewire(Onboarding::class)
        ->assertSuccessful()
        ->assertSee($expected);
})->with([
    'not connected' => [[], 'Connect with Stripe'],
    'connected, charges not enabled' => [
        ['stripe_connect_id' => 'acct_test', 'stripe_connect_charges_enabled' => '0'],
        'Stripe Connected, charges not enabled yet',
    ],
    'connected, charges enabled' => [
        ['stripe_connect_id' => 'acct_test', 'stripe_connect_charges_enabled' => '1'],
        'ready to accept payments',
    ],
]);

test('the onboarding redirect lands on a page that renders', function () {
    $response = (new EnsureOnboardingComplete)->handle(
        Request::create('/admin/dashboard'),
        fn () => new Response('OK'),
    );

    expect($response->getStatusCode())->toBe(302)
        ->and(parse_url((string) $response->headers->get('Location'), PHP_URL_PATH))
        ->toBe(parse_url(Onboarding::getUrl(), PHP_URL_PATH));

    livewire(Onboarding::class)
        ->assertSuccessful();
});

test('onboarding stores an uploaded logo on the public disk so the bakery logo URL resolves', function () {
    Storage::fake('local');
    Storage::fake('public');

    livewire(Onboarding::class)
        ->set('branding.store_logo', [UploadedFile::fake()->image('logo.png')])
        ->goToWizardStep(4)
        ->assertHasNoErrors();

    $storedLogo = settings('store_logo');

    expect($storedLogo)->toStartWith('logos/')
        ->and(Storage::disk('public')->exists($storedLogo))->toBeTrue()
        ->and(Storage::disk('local')->exists($storedLogo))->toBeFalse()
        ->and(StoreInfo::resolve()->logoUrl())->toBe(asset("storage/{$storedLogo}"));
});

test('onboarding keeps the existing logo when the branding step is completed again', function () {
    settings(['store_logo' => 'logos/existing.png']);

    livewire(Onboarding::class)
        ->goToWizardStep(4)
        ->assertHasNoErrors();

    expect(settings('store_logo'))->toBe('logos/existing.png');
});

test('onboarding rejects a logo path that was not uploaded or already stored', function () {
    settings(['store_logo' => 'logos/existing.png']);

    livewire(Onboarding::class)
        ->set('branding.store_logo', ['logos/someone-elses-file.png'])
        ->goToWizardStep(4)
        ->assertHasErrors(['branding.store_logo']);

    expect(settings('store_logo'))->toBe('logos/existing.png');
});

test('onboarding page sends a finished bakery back to the dashboard', function () {
    settings(['onboarding_completed_at' => '2026-10-05T12:00:00.000000Z']);

    livewire(Onboarding::class)
        ->assertRedirect(url('/admin'));
});
