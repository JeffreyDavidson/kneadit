<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;

class SitemapController extends Controller
{
    public function index()
    {
        $posts = BlogPost::published()->orderByDesc('published_at')->get();

        return response()
            ->view('sitemap', compact('posts'))
            ->header('Content-Type', 'text/xml');
    }
}
