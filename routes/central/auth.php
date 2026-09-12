<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\SendVerificationNotificationController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::get('register', [RegisterController::class, 'show'])->name('register')->middleware('guest');
    Route::post('register', [RegisterController::class, 'store'])->middleware(['guest', 'throttle:sensitive-write']);
    Route::redirect('login', '/')->name('login')->middleware('guest');
    Route::post('logout', LogoutController::class)->name('logout')->middleware('auth');

    Route::view('email/verify', 'auth.verify-email')->middleware('auth')->name('verification.notice');
    Route::get('email/verify/{id}/{hash}', VerifyEmailController::class)->middleware(['auth', 'signed'])->name('verification.verify');
    Route::post('email/verification-notification', SendVerificationNotificationController::class)->middleware(['auth', 'throttle:verification-resend'])->name('verification.send');

    Route::get('forgot-password', [ForgotPasswordController::class, 'show'])->name('password.request')->middleware('guest');
    Route::post('forgot-password', [ForgotPasswordController::class, 'store'])->name('password.email')->middleware(['guest', 'throttle:sensitive-write']);
    Route::get('reset-password/{token}', [ResetPasswordController::class, 'show'])->name('password.reset')->middleware('guest');
    Route::post('reset-password', [ResetPasswordController::class, 'store'])->name('password.update')->middleware(['guest', 'throttle:sensitive-write']);
});
