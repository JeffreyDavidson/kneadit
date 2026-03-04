<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ContactController;

require __DIR__.'/billing.php';

// Storefront Routes
Route::get('/', [OrderController::class, 'home'])->name('home');
Route::get('/order', [OrderController::class, 'index'])->name('order');
Route::post('/order', [OrderController::class, 'store'])->name('order.store');
Route::get('/order/confirmation/{orderNumber}', [OrderController::class, 'confirmation'])->name('order.confirmation');

// AJAX Routes
Route::post('/coupon/apply', [OrderController::class, 'applyCoupon'])->name('coupon.apply');
Route::get('/capacity/check/{date}', [OrderController::class, 'checkCapacity'])->name('capacity.check');
Route::post('/favorites/toggle', [OrderController::class, 'toggleFavorite'])->name('favorites.toggle');
Route::get('/favorites', [OrderController::class, 'getFavorites'])->name('favorites.get');

// Contact Routes
Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');