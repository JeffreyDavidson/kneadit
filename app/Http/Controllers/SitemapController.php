<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $posts = BlogPost::published()->orderByDesc('published_at')->get();

        return response()
            ->view('sitemap', [
                'posts' => $posts,
            ])
            ->header('Content-Type', 'text/xml');
    }
}
