<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;

class BlogController extends Controller
{
    public function index()
    {
        $posts = BlogPost::published()
            ->orderByDesc('published_at')
            ->paginate(12);

        $categories = [
            'all' => 'All Posts',
            'guides' => 'Getting Started',
            'laws' => 'Cottage Food Laws',
            'tips' => 'Baker Tips',
            'news' => 'KneadIt News',
        ];

        $activeCategory = request('category', 'all');

        if ($activeCategory !== 'all') {
            $posts = BlogPost::published()
                ->where('category', $activeCategory)
                ->orderByDesc('published_at')
                ->paginate(12);
        }

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
}
