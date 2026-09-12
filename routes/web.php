<?php

use App\Routing\Resolvers\PublishedBlogPostResolver;
use Illuminate\Support\Facades\Route;

Route::bind('centralPost', resolve(PublishedBlogPostResolver::class));

require __DIR__ . '/billing.php';
require __DIR__ . '/central/auth.php';
require __DIR__ . '/central/platform.php';
require __DIR__ . '/central/marketing.php';
require __DIR__ . '/central/seo.php';
