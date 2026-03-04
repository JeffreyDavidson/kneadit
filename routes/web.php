<?php

use Illuminate\Support\Facades\Route;

require __DIR__.'/billing.php';

Route::get('/', function () {
    return view('welcome');
});
