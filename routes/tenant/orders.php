<?php

declare(strict_types=1);

use App\Http\Controllers\Order\ApplyCouponController;
use App\Http\Controllers\Order\ApplyGiftCardController;
use App\Http\Controllers\Order\AvailabilityController;
use App\Http\Controllers\Order\CapacityController;
use App\Http\Controllers\Order\MessageController;
use App\Http\Controllers\Order\ModifyOrderController;
use App\Http\Controllers\Order\PickupSlotsController;
use App\Http\Controllers\Order\ReorderController;
use App\Http\Controllers\Order\StripeCancelController;
use App\Http\Controllers\Order\StripeSuccessController;
use App\Http\Controllers\Order\TrackingController;
use App\Http\Controllers\Order\VerifyOrderAccessController;
use App\Http\Controllers\Storefront\ShowOrderConfirmationController;
use App\Http\Controllers\Storefront\ShowOrderFormController;
use App\Http\Controllers\Storefront\SubmitOrderController;
use Illuminate\Support\Facades\Route;

Route::get('order', ShowOrderFormController::class)->name('order.create');
Route::post('order', SubmitOrderController::class)->name('order.store')->middleware('throttle:10,1');

// Email-verification gate for order-by-number access (must be reachable
// without the gate, since the whole point is to grant access).
Route::get('order/{order:order_number}/verify', [VerifyOrderAccessController::class, 'show'])->name('order.verify.show');
Route::post('order/{order:order_number}/verify', [VerifyOrderAccessController::class, 'store'])->name('order.verify.store')->middleware('throttle:5,1');

// Stripe redirect callbacks — handlers grant session access on success/cancel.
Route::get('order/stripe/success/{order:order_number}', StripeSuccessController::class)->name('order.stripe.success');
Route::get('order/stripe/cancel/{order:order_number}', StripeCancelController::class)->name('order.stripe.cancel');

Route::get('track', [TrackingController::class, 'show'])->name('order.track');
Route::post('track', [TrackingController::class, 'store'])->name('order.track.lookup')->middleware('throttle:10,1');

// Order-by-number views require session-verified ownership.
Route::middleware('order.access')->group(function () {
    Route::get('order/confirmation/{order:order_number}', ShowOrderConfirmationController::class)->name('order.confirmation');
    Route::get('order/{order:order_number}/messages', [MessageController::class, 'show'])->name('order.messages');
    Route::post('order/{order:order_number}/messages', [MessageController::class, 'store'])->name('order.messages.send')->middleware('throttle:10,1');
    Route::get('order/reorder/{order:order_number}', ReorderController::class)->name('order.reorder');
    Route::post('order/{order:order_number}/modify', ModifyOrderController::class)->name('order.modify')->middleware('throttle:5,1');
});

// Capacity check (AJAX)
Route::get('capacity/check/{date}', CapacityController::class)->name('capacity.check');

// Availability for next 30 days (scheduling integration)
Route::get('availability', AvailabilityController::class)->name('order.availability');

// Pickup time slots for a given date (when pickup_slots_enabled).
Route::get('pickup-slots/{date}', PickupSlotsController::class)
    ->name('order.pickupSlots')
    ->where('date', '[0-9]{4}-[0-9]{2}-[0-9]{2}')
    ->middleware('throttle:60,1');

// Coupon validation (AJAX)
Route::post('coupon/apply', ApplyCouponController::class)->name('coupon.apply')->middleware('throttle:10,1');

// Gift card validation (AJAX)
Route::post('gift-card/apply', ApplyGiftCardController::class)->name('giftCard.apply')->middleware('throttle:10,1');
