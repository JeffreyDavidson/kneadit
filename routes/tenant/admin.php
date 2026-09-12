<?php

declare(strict_types=1);

use App\Http\Controllers\Central\InvoiceController;
use App\Http\Controllers\Central\PrintProductLabelController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('orders/{order:order_number}/invoice', InvoiceController::class)->name('orders.invoice');
    Route::get('products/{product}/label', PrintProductLabelController::class)->name('products.label');
});
