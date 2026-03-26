<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use Illuminate\Contracts\View\View;

class BlogController extends Controller
{
    public function index(): View
    {
        $categories = [
            'all' => 'All Posts',
            'guides' => 'Getting Started',
            'tips' => 'Baker Tips',
            'news' => 'News',
            'recipes' => 'Recipes',
        ];

        $activeCategory = request('category', 'all');

        $query = BlogPost::query()->published()->latest('published_at');

        if ($activeCategory !== 'all') {
            $query->where('category', $activeCategory);
        }

        $posts = $query->paginate(6);

        return view('blog.index', compact('posts', 'categories', 'activeCategory'));
    }

    public function show(BlogPost $post): View
    {
        $related = BlogPost::query()->published()
            ->where('id', '!=', $post->id)
            ->latest('published_at')
            ->take(3)
            ->get();

        return view('blog.show', compact('post', 'related'));
    }
}
