<?php

namespace App\Http\Controllers;

use App\Enums\BlogPostCategory;
use App\Http\Requests\IndexBlogPostsRequest;
use App\Models\BlogPost;
use Illuminate\Contracts\View\View;

class BlogController extends Controller
{
    public function index(IndexBlogPostsRequest $request): View
    {
        $categories = BlogPostCategory::options();
        $activeCategory = $request->validated('category', 'all');
        $posts = BlogPost::query()->forListing($activeCategory)->paginate(18);

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
