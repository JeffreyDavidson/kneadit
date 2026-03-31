<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use Illuminate\Http\Response;

class BlogFeedController extends Controller
{
    public function __invoke(): Response
    {
        $posts = BlogPost::query()->published()
            ->orderByDesc('published_at')
            ->take(20)
            ->get();

        return response()
            ->view('blog.central-feed', [
                'posts' => $posts,
            ])
            ->header('Content-Type', 'application/rss+xml; charset=UTF-8');
    }
}
