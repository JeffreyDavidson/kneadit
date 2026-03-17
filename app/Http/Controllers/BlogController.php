<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use Illuminate\Contracts\View\View;

class BlogController extends Controller
{
    public function index(): View
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

    public function show(string $slug): View
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
