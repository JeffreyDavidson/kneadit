<?php

declare(strict_types=1);

use App\Http\Controllers\Storefront\Account\CustomerDashboardController;
use App\Http\Controllers\Storefront\Account\LoginCustomerController;
use App\Http\Controllers\Storefront\Account\LogoutCustomerController;
use App\Http\Controllers\Storefront\Account\OrderHistoryController;
use App\Http\Controllers\Storefront\Account\ProfileController;
use App\Http\Controllers\Storefront\Account\RegisterCustomerController;
use App\Http\Controllers\Storefront\Account\ResendCustomerVerificationController;
use App\Http\Controllers\Storefront\Account\ResetPasswordController;
use App\Http\Controllers\Storefront\Account\SendPasswordResetLinkController;
use App\Http\Controllers\Storefront\Account\ShowEmailVerifyNoticeController;
use App\Http\Controllers\Storefront\Account\ShowResetPasswordController;
use App\Http\Controllers\Storefront\Account\VerifyCustomerEmailController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest:customer')->group(function () {
    Route::view('account/register', 'storefront.account.register')->name('account.register.show');
    Route::post('account/register', RegisterCustomerController::class)->name('account.register')->middleware('throttle:sensitive-write');

    Route::view('account/login', 'storefront.account.login')->name('account.login.show');
    Route::post('account/login', LoginCustomerController::class)->name('account.login')->middleware('throttle:sensitive-write');

    Route::view('account/forgot-password', 'storefront.account.forgot-password')->name('account.password.request');
    Route::post('account/forgot-password', SendPasswordResetLinkController::class)->name('account.password.email')->middleware('throttle:sensitive-write');

    Route::get('account/password/reset/{token}', ShowResetPasswordController::class)->name('account.password.reset');
    Route::post('account/password/reset', ResetPasswordController::class)->name('account.password.update')->middleware('throttle:sensitive-write');
});

Route::middleware('auth:customer')->group(function () {
    Route::get('account', CustomerDashboardController::class)->name('account.dashboard');
    Route::get('account/orders', OrderHistoryController::class)->name('account.orders');
    Route::get('account/profile', [ProfileController::class, 'show'])->name('account.profile.show');
    Route::post('account/profile', [ProfileController::class, 'update'])->name('account.profile.update')->middleware('throttle:form-write');
    Route::post('account/logout', LogoutCustomerController::class)->name('account.logout');

    Route::get('account/email/verify', ShowEmailVerifyNoticeController::class)->name('account.email.verify.notice');
    Route::get('account/email/verify/{id}/{hash}', VerifyCustomerEmailController::class)
        ->middleware('signed')
        ->name('account.email.verify');
    Route::post('account/email/verification-notification', ResendCustomerVerificationController::class)
        ->middleware('throttle:verification-resend')
        ->name('account.email.verify.send');
});
