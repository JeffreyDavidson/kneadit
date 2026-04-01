<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Content\BlogPost;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $posts = BlogPost::query()->published()->orderByDesc('published_at')->get();

        return response()
            ->view('platform.sitemap', [
                'posts' => $posts,
            ])
            ->header('Content-Type', 'text/xml');
    }
}
