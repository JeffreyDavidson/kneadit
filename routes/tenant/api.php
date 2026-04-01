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

// Read endpoints — generous limit
Route::middleware('throttle:60,1')->group(function () {
    Route::get('store', StoreInfoController::class);
    Route::get('categories', ApiCategoryController::class);
    Route::get('products', ApiProductController::class);
    Route::get('menu', ApiMenuController::class);
    Route::get('capacity/{date}', ApiCapacityController::class);
    Route::get('reviews', [ApiReviewController::class, 'index']);
    Route::get('gallery', ApiGalleryController::class);
    Route::get('favorites', [ApiFavoriteController::class, 'index'])->name('api.favorites.index');
});

// Write endpoints — tighter limit
Route::middleware('throttle:10,1')->group(function () {
    Route::post('orders', ApiOrderController::class);
    Route::post('coupon/validate', CouponValidationController::class);
    Route::post('reviews', [ApiReviewController::class, 'store']);
    Route::post('contact', ApiContactController::class);
    Route::post('favorites/toggle', [ApiFavoriteController::class, 'store'])->name('api.favorites.toggle');
    Route::post('waitlist', ApiWaitlistController::class);
});
