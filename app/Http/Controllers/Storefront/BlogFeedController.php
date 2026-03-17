<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\Setting;

class BlogFeedController extends Controller
{
    /**
     * Generate the RSS feed for the storefront blog.
     */
    public function __invoke()
    {
        $posts = BlogPost::where('is_published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->latest('published_at')
            ->take(20)
            ->get();

        $storeName = Setting::get('store_name', 'Our Bakery');

        return response()
            ->view('blog.feed', compact('posts', 'storeName'))
            ->header('Content-Type', 'application/rss+xml; charset=UTF-8');
    }
}
