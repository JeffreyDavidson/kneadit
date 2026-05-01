<?php

declare(strict_types=1);

use App\Http\Controllers\Api\CapacityController as ApiCapacityController;
use App\Http\Controllers\Api\CategoryController as ApiCategoryController;
use App\Http\Controllers\Api\ContactController as ApiContactController;
use App\Http\Controllers\Api\CouponValidationController;
use App\Http\Controllers\Api\FavoriteController as ApiFavoriteController;
use App\Http\Controllers\Api\GalleryController as ApiGalleryController;
use App\Http\Controllers\Api\MenuController as ApiMenuController;
use App\Http\Controllers\Api\OrderController as ApiOrderController;
use App\Http\Controllers\Api\ProductController as ApiProductController;
use App\Http\Controllers\Api\ReviewController as ApiReviewController;
use App\Http\Controllers\Api\StoreInfoController;
use App\Http\Controllers\Api\WaitlistController as ApiWaitlistController;
use Illuminate\Support\Facades\Route;

Route::name('api.')->group(function () {
    // Read endpoints — generous limit
    Route::middleware('throttle:frequent-poll')->group(function () {
        Route::get('store', StoreInfoController::class)->name('store');
        Route::get('categories', ApiCategoryController::class)->name('categories.index');
        Route::get('products', ApiProductController::class)->name('products.index');
        Route::get('menu', ApiMenuController::class)->name('menu');
        Route::get('capacity/{date}', ApiCapacityController::class)->name('capacity.show');
        Route::get('reviews', [ApiReviewController::class, 'index'])->name('reviews.index');
        Route::get('gallery', ApiGalleryController::class)->name('gallery.index');
        Route::get('favorites', [ApiFavoriteController::class, 'index'])->name('favorites.index');
    });

    // Write endpoints — tighter limit
    Route::middleware('throttle:form-write')->group(function () {
        Route::post('orders', ApiOrderController::class)->name('orders.store');
        Route::post('coupon/validate', CouponValidationController::class)->name('coupon.validate');
        Route::post('reviews', [ApiReviewController::class, 'store'])->name('reviews.store');
        Route::post('contact', ApiContactController::class)->name('contact.store');
        Route::post('favorites/toggle', [ApiFavoriteController::class, 'store'])->name('favorites.toggle');
        Route::post('waitlist', ApiWaitlistController::class)->name('waitlist.store');
    });
});
