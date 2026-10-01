<?php

use App\Models\Staff\User;
use App\Services\Tenants\Contracts\LegacyCatalogImporter;
use App\Services\Tenants\Contracts\LegacyCouponImporter;
use App\Services\Tenants\Contracts\LegacyCustomerImporter;
use App\Services\Tenants\Contracts\LegacyEngagementImporter;
use App\Services\Tenants\Contracts\LegacyFinancialImporter;
use App\Services\Tenants\Contracts\LegacyOrderImporter;
use App\Services\Tenants\Contracts\LegacyOrderItemImporter;
use App\Services\Tenants\Contracts\LegacyRecipeImporter;
use App\Services\Tenants\Contracts\LegacyReviewImporter;
use App\Services\Tenants\Contracts\LegacySchedulingImporter;
use App\Services\Tenants\Contracts\LegacySettingsImporter;
use App\Services\Tenants\DatabaseLegacyCatalogImporter;
use App\Services\Tenants\DatabaseLegacyCouponImporter;
use App\Services\Tenants\DatabaseLegacyCustomerImporter;
use App\Services\Tenants\DatabaseLegacyEngagementImporter;
use App\Services\Tenants\DatabaseLegacyFinancialImporter;
use App\Services\Tenants\DatabaseLegacyOrderImporter;
use App\Services\Tenants\DatabaseLegacyOrderItemImporter;
use App\Services\Tenants\DatabaseLegacyRecipeImporter;
use App\Services\Tenants\DatabaseLegacyReviewImporter;
use App\Services\Tenants\DatabaseLegacySchedulingImporter;
use App\Services\Tenants\DatabaseLegacySettingsImporter;
use App\Support\Csp\CspNonce;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Cache\RateLimiting\Unlimited;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Cashier\Cashier;
use Laravel\Pennant\Feature;
use Livewire\Livewire;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomainOrSubdomain;
use Stripe\StripeClient;

dataset('named rate limiters', [
    'webhooks' => ['webhooks', 30],
    'sensitive-write' => ['sensitive-write', 5],
    'verification-resend' => ['verification-resend', 6],
    'form-write' => ['form-write', 10],
    'referral-claim' => ['referral-claim', 30],
    'frequent-poll' => ['frequent-poll', 60],
]);

dataset('legacy importer bindings', [
    'catalog' => [LegacyCatalogImporter::class, DatabaseLegacyCatalogImporter::class],
    'coupon' => [LegacyCouponImporter::class, DatabaseLegacyCouponImporter::class],
    'customer' => [LegacyCustomerImporter::class, DatabaseLegacyCustomerImporter::class],
    'engagement' => [LegacyEngagementImporter::class, DatabaseLegacyEngagementImporter::class],
    'financial' => [LegacyFinancialImporter::class, DatabaseLegacyFinancialImporter::class],
    'order item' => [LegacyOrderItemImporter::class, DatabaseLegacyOrderItemImporter::class],
    'order' => [LegacyOrderImporter::class, DatabaseLegacyOrderImporter::class],
    'review' => [LegacyReviewImporter::class, DatabaseLegacyReviewImporter::class],
    'recipe' => [LegacyRecipeImporter::class, DatabaseLegacyRecipeImporter::class],
    'scheduling' => [LegacySchedulingImporter::class, DatabaseLegacySchedulingImporter::class],
    'settings' => [LegacySettingsImporter::class, DatabaseLegacySettingsImporter::class],
]);

test('named rate limiter resolves and enforces its per-minute limit', function (string $name, int $maxAttempts) {
    // Arrange
    $request = Request::create('https://example.test/anything');

    // Act
    $limiter = RateLimiter::limiter($name);

    // Assert
    expect($limiter)->not->toBeNull();

    $limit = $limiter($request);

    expect($limit)->toBeInstanceOf(Limit::class)
        ->and($limit->maxAttempts)->toBe($maxAttempts)
        ->and($limit->decaySeconds)->toBe(60);
})->with('named rate limiters');

test('named rate limiters are bypassed for the browser-test host only', function (string $name) {
    // Arrange
    $bypassed = Request::create('https://browser-test.kneadit.test/anything');
    $normal = Request::create('https://example.test/anything');

    // Act
    $limiter = RateLimiter::limiter($name);

    // Assert
    expect($limiter($bypassed))->toBeInstanceOf(Unlimited::class)
        ->and($limiter($normal))->toBeInstanceOf(Limit::class);
})->with([
    'sensitive-write',
    'verification-resend',
    'form-write',
    'referral-claim',
    'frequent-poll',
]);

test('named rate limiters key by user id, falling back to the client ip', function () {
    // Arrange
    $user = User::factory()->make(['id' => 42]);
    $guest = Request::create('https://example.test/anything', server: ['REMOTE_ADDR' => '203.0.113.9']);
    $authenticated = Request::create('https://example.test/anything');
    $authenticated->setUserResolver(fn () => $user);

    // Act
    $limiter = RateLimiter::limiter('form-write');

    // Assert
    expect($limiter($guest)->key)->toBe('203.0.113.9')
        ->and($limiter($authenticated)->key)->toBe('42');
});

test('stripe client resolves to a stripe client', function () {
    expect(app(StripeClient::class))->toBeInstanceOf(StripeClient::class);
});

test('csp nonce is scoped to the request lifecycle', function () {
    // Arrange
    $first = app(CspNonce::class);

    // Act
    $second = app(CspNonce::class);

    // Assert
    expect($second)->toBe($first);

    app()->forgetScopedInstances();

    expect(app(CspNonce::class))->not->toBe($first);
});

test('legacy importer contract resolves to its database implementation', function (string $contract, string $concrete) {
    expect(app($contract))->toBeInstanceOf($concrete);
})->with('legacy importer bindings');

test('infrastructure hooks are registered', function () {
    expect(Cashier::$customerModel)->toBe(User::class)
        ->and(Model::preventsLazyLoading())->toBeTrue();
});

test('plan feature flags and tenancy-aware livewire middleware are registered', function () {
    expect(Feature::defined())->toContain('growth-features', 'pro-features')
        ->and(Livewire::getPersistentMiddleware())->toContain(InitializeTenancyByDomainOrSubdomain::class);
});
