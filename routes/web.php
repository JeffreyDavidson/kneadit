<?php

use App\Routing\Bindings\PublishedBlogPostResolver;
use Illuminate\Support\Facades\Route;

Route::bind('centralPost', function (string $slug) {
    return resolve(PublishedBlogPostResolver::class)($slug);
});

require __DIR__.'/billing.php';
require __DIR__.'/central/auth.php';
require __DIR__.'/central/platform.php';
require __DIR__.'/central/marketing.php';
require __DIR__.'/central/seo.php';
