<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $posts = BlogPost::query()->published()->orderByDesc('published_at')->get();

        return response()
            ->view('sitemap', [
                'posts' => $posts,
            ])
            ->header('Content-Type', 'text/xml');
    }
}
