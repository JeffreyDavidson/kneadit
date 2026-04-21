<?php

declare(strict_types=1);

use App\Http\Controllers\Order\ApplyCouponController;
use App\Http\Controllers\Order\ApplyGiftCardController;
use App\Http\Controllers\Order\AvailabilityController;
use App\Http\Controllers\Order\CapacityController;
use App\Http\Controllers\Order\MessageController;
use App\Http\Controllers\Order\ReorderController;
use App\Http\Controllers\Order\StripeCancelController;
use App\Http\Controllers\Order\StripeSuccessController;
use App\Http\Controllers\Order\TrackingController;
use App\Http\Controllers\Storefront\ShowOrderConfirmationController;
use App\Http\Controllers\Storefront\ShowOrderFormController;
use App\Http\Controllers\Storefront\SubmitOrderController;
use Illuminate\Support\Facades\Route;

Route::get('order', ShowOrderFormController::class)->name('order.create');
Route::post('order', SubmitOrderController::class)->name('order.store')->middleware('throttle:10,1');
Route::get('order/confirmation/{order:order_number}', ShowOrderConfirmationController::class)->name('order.confirmation');
Route::get('order/stripe/success/{order:order_number}', StripeSuccessController::class)->name('order.stripe.success');
Route::get('order/stripe/cancel/{order:order_number}', StripeCancelController::class)->name('order.stripe.cancel');
Route::get('track', [TrackingController::class, 'show'])->name('order.track');
Route::post('track', [TrackingController::class, 'store'])->name('order.track.lookup')->middleware('throttle:10,1');

// Order messages
Route::get('order/{order:order_number}/messages', [MessageController::class, 'show'])->name('order.messages');
Route::post('order/{order:order_number}/messages', [MessageController::class, 'store'])->name('order.messages.send')->middleware('throttle:10,1');

// Reorder data (AJAX)
Route::get('order/reorder/{order:order_number}', ReorderController::class)->name('order.reorder');

// Capacity check (AJAX)
Route::get('capacity/check/{date}', CapacityController::class)->name('capacity.check');

// Availability for next 30 days (scheduling integration)
Route::get('availability', AvailabilityController::class)->name('order.availability');

// Coupon validation (AJAX)
Route::post('coupon/apply', ApplyCouponController::class)->name('coupon.apply')->middleware('throttle:10,1');

// Gift card validation (AJAX)
Route::post('gift-card/apply', ApplyGiftCardController::class)->name('giftCard.apply')->middleware('throttle:10,1');
