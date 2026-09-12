<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\AcceptInvitationController;
use App\Http\Controllers\Auth\ShowInvitationController;
use App\Http\Controllers\Central\ConsumeImpersonationController;
use App\Http\Controllers\Marketing\PreviewCustomerCampaignController;
use App\Http\Controllers\Storefront\AppIconController;
use App\Http\Controllers\Storefront\DriverDashboardController;
use App\Http\Controllers\Storefront\ManifestController;
use App\Http\Controllers\Storefront\MarkOrderDeliveredController;
use App\Http\Controllers\Stripe\StripeConnectController;
use App\Http\Middleware\ResolveInvitation;
use Illuminate\Support\Facades\Route;

Route::get('manifest.json', ManifestController::class)->name('manifest');
Route::get('icons/icon-{size}.png', AppIconController::class)->name('app.icon');

Route::get('impersonate/{token}', ConsumeImpersonationController::class)->name('impersonate.consume');
Route::get('stripe/connect', StripeConnectController::class)->middleware('auth')->name('stripe.connect');
Route::get('admin/campaigns/{campaign}/preview', PreviewCustomerCampaignController::class)
    ->middleware(['auth', 'can:manager-staff'])
    ->name('campaign.preview');

Route::prefix('driver')->name('driver.')->group(function () {
    Route::get('/', DriverDashboardController::class)->name('index');
    Route::post('{order:order_number}/delivered', MarkOrderDeliveredController::class)->name('delivered')->middleware('auth');
});

Route::get('invite/{token}', ShowInvitationController::class)->name('invitation.show')->middleware(ResolveInvitation::class);
Route::post('invite/{token}', AcceptInvitationController::class)->name('invitation.accept')->middleware([ResolveInvitation::class, 'throttle:sensitive-write']);
