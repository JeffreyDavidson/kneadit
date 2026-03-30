<?php

namespace App\Http\Controllers\Storefront;

use App\Enums\BlogPostCategory;
use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use Illuminate\Contracts\View\View;

class BlogController extends Controller
{
    public function index(): View
    {
        $categories = BlogPostCategory::options();
        $activeCategory = request('category', 'all');
        $posts = BlogPost::query()->forListing($activeCategory)->paginate(6);

        return view('blog.index', [
            'posts' => $posts,
            'categories' => $categories,
            'activeCategory' => $activeCategory,
        ]);
    }

    public function show(BlogPost $post): View
    {
        $related = BlogPost::query()->published()->relatedTo($post)->get();

        return view('blog.show', [
            'post' => $post,
            'related' => $related,
        ]);
    }
}
