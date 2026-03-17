<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;

class BlogController extends Controller
{
    /**
     * Show the storefront blog listing page.
     */
    public function index()
    {
        $categories = [
            'all' => 'All Posts',
            'guides' => 'Getting Started',
            'tips' => 'Baker Tips',
            'news' => 'News',
            'recipes' => 'Recipes',
        ];

        $activeCategory = request('category', 'all');

        $query = BlogPost::where('is_published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->latest('published_at');

        if ($activeCategory !== 'all') {
            $query->where('category', $activeCategory);
        }

        $posts = $query->paginate(6);

        return view('blog.index', compact('posts', 'categories', 'activeCategory'));
    }

    /**
     * Show a single storefront blog post.
     */
    public function show($slug)
    {
        $post = BlogPost::where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        $related = BlogPost::where('is_published', true)
            ->where('id', '!=', $post->id)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->latest('published_at')
            ->take(3)
            ->get();

        return view('blog.show', compact('post', 'related'));
    }
}
