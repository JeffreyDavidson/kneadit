<?php

declare(strict_types=1);

use App\Http\Controllers\Central\ConsumeImpersonationController;
use App\Http\Controllers\Stripe\StripeConnectController;
use App\Http\Controllers\Tenant\DomainProofController;
use App\Http\Controllers\Tenant\Invitations\AcceptInvitationController;
use App\Http\Controllers\Tenant\Invitations\ShowInvitationController;
use App\Http\Controllers\Tenant\Marketing\EmailUnsubscribesController;
use App\Http\Controllers\Tenant\Marketing\PreviewCustomerCampaignController;
use App\Http\Controllers\Tenant\Storefront\AppIconController;
use App\Http\Controllers\Tenant\Storefront\DriverDashboardController;
use App\Http\Controllers\Tenant\Storefront\ManifestController;
use App\Http\Controllers\Tenant\Storefront\MarkOrderDeliveredController;
use App\Http\Middleware\ResolveInvitation;
use App\Services\Platform\CustomDomainProof;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Route;

// Ownership proof for custom domains served through a proxy. Outside the storefront-enabled
// check so a paused storefront does not flip its domain to unverified.
Route::get(CustomDomainProof::PATH, DomainProofController::class)->name('domainProof');

Route::get('manifest.json', ManifestController::class)->name('manifest');
Route::get('icons/icon-{size}.png', AppIconController::class)->name('app.icon');

Route::get('impersonate/{token}', ConsumeImpersonationController::class)->name('impersonate.consume');
Route::get('stripe/connect', StripeConnectController::class)->middleware(['auth', 'can:manage-payments'])->name('stripe.connect');
Route::get('admin/campaigns/{campaign}/preview', PreviewCustomerCampaignController::class)
    ->middleware(['auth', 'can:manager-staff'])
    ->name('campaign.preview');

// Unsubscribe link in marketing emails. Signed against the path only, never expires, and sits
// outside the storefront-enabled check so opting out always works. Mail providers POST here for
// RFC 8058 one-click unsubscribe without a CSRF token; the signature is the protection.
Route::middleware('signed:relative')->group(function () {
    Route::post('email/unsubscribe/{customer}', [EmailUnsubscribesController::class, 'store'])
        ->withoutMiddleware(PreventRequestForgery::class)
        ->name('emailUnsubscribe.store');
    Route::get('email/unsubscribe/{customer}', [EmailUnsubscribesController::class, 'show'])->name('emailUnsubscribe.show');
    Route::delete('email/unsubscribe/{customer}', [EmailUnsubscribesController::class, 'destroy'])->name('emailUnsubscribe.destroy');
});

// Lists customers' names, addresses and notes, so both routes need a signed-in staff member.
Route::prefix('driver')->name('driver.')->middleware('auth')->group(function () {
    Route::get('/', DriverDashboardController::class)->name('index');
    Route::post('{order:order_number}/delivered', MarkOrderDeliveredController::class)->name('delivered');
});

Route::get('invite/{token}', ShowInvitationController::class)->name('invitation.show')->middleware(ResolveInvitation::class);
Route::post('invite/{token}', AcceptInvitationController::class)->name('invitation.accept')->middleware([ResolveInvitation::class, 'throttle:sensitive-write']);
