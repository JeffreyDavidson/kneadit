<?php

namespace App\Http\Controllers;

use App\Enums\BlogPostCategory;
use App\Models\BlogPost;
use Illuminate\Contracts\View\View;

class BlogController extends Controller
{
    public function index(): View
    {
        $categories = BlogPostCategory::options();
        $activeCategory = request('category', 'all');
        $posts = BlogPost::query()->forListing($activeCategory)->paginate(18);

        return view('blog.index', compact('posts', 'categories', 'activeCategory'));
    }

    public function show(string $slug): View
    {
        $post = BlogPost::query()->published()->where('slug', $slug)->firstOrFail();
        $related = BlogPost::query()->published()->relatedTo($post)->get();

        return view('blog.show', compact('post', 'related'));
    }
}
