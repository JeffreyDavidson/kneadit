<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;

class BlogController extends Controller
{
    public function index()
    {
        $categories = [
            'all' => 'All Posts',
            'guides' => 'Getting Started',
            'laws' => 'Cottage Food Laws',
            'tips' => 'Baker Tips',
            'news' => 'KneadIt News',
        ];

        $activeCategory = request('category', 'all');

        $query = BlogPost::published()->orderByDesc('published_at');

        if ($activeCategory !== 'all') {
            $query->where('category', $activeCategory);
        }

        $posts = $query->paginate(18);

        return view('blog.index', compact('posts', 'categories', 'activeCategory'));
    }

    public function show(string $slug)
    {
        $post = BlogPost::published()->where('slug', $slug)->firstOrFail();

        $related = BlogPost::published()
            ->where('id', '!=', $post->id)
            ->where('category', $post->category)
            ->limit(3)
            ->get();

        return view('blog.show', compact('post', 'related'));
    }

    public function feed()
    {
        $posts = BlogPost::published()
            ->orderByDesc('published_at')
            ->take(20)
            ->get();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">';
        $xml .= '<channel>';
        $xml .= '<title>KneadIt — Resources for Cottage Food Bakers</title>';
        $xml .= '<link>https://getkneadit.app/resources</link>';
        $xml .= '<description>Guides, tips, and resources for cottage food bakers.</description>';
        $xml .= '<language>en-us</language>';
        $xml .= '<atom:link href="' . url('/resources/feed.xml') . '" rel="self" type="application/rss+xml"/>';

        foreach ($posts as $post) {
            $xml .= '<item>';
            $xml .= '<title>' . htmlspecialchars($post->title) . '</title>';
            $xml .= '<link>' . url("/resources/{$post->slug}") . '</link>';
            $xml .= '<guid isPermaLink="true">' . url("/resources/{$post->slug}") . '</guid>';
            $xml .= '<description>' . htmlspecialchars($post->excerpt ?? strip_tags(substr($post->body, 0, 300))) . '</description>';
            $xml .= '<pubDate>' . $post->published_at->toRfc2822String() . '</pubDate>';
            $xml .= '<category>' . htmlspecialchars($post->category) . '</category>';
            $xml .= '</item>';
        }

        $xml .= '</channel></rss>';

        return response($xml, 200, ['Content-Type' => 'application/rss+xml; charset=UTF-8']);
    }
}
