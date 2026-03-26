<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use Illuminate\Http\Response;

class BlogFeedController extends Controller
{
    /**
     * Generate the RSS feed for the storefront blog.
     */
    public function __invoke(): Response
    {
        $posts = BlogPost::query()->where('is_published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->latest('published_at')
            ->take(20)
            ->get();

        $storeName = settings('store_name', 'Our Bakery');

        return response()
            ->view('blog.feed', compact('posts', 'storeName'))
            ->header('Content-Type', 'application/rss+xml; charset=UTF-8');
    }
}
