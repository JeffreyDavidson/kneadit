<?php

use App\Http\Controllers\Central\InvoiceController;
use App\Http\Controllers\Central\PrintProductLabelController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Here are admin-specific routes that are separate from the main web routes.
|
*/

Route::middleware(['web', 'auth'])->prefix('admin')->name('admin.')->group(function () {
    // Invoice routes
    Route::get('orders/{order:order_number}/invoice', InvoiceController::class)->name('orders.invoice');

    // Printable compliance label for a product
    Route::get('products/{product}/label', PrintProductLabelController::class)->name('products.label');
});
