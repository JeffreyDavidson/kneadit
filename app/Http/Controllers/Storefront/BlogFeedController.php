<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Content\TenantBlogPost;
use App\Services\Settings\TenantSettings;
use Illuminate\Http\Response;

class BlogFeedController extends Controller
{
    public function __invoke(TenantSettings $settings): Response
    {
        $posts = TenantBlogPost::query()
            ->published()
            ->latest('published_at')
            ->take(20)
            ->get();

        return response()
            ->view('storefront.blog.feed', [
                'settings' => $settings,
                'posts' => $posts,
            ])
            ->header('Content-Type', 'application/rss+xml; charset=UTF-8');
    }
}
