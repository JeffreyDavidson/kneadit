<?php

namespace App\Http\Controllers\Central;

use App\Enums\Content\BlogPostCategory;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\IndexBlogPostsRequest;
use App\Models\Content\BlogPost;
use Illuminate\Contracts\View\View;

class BlogController extends Controller
{
    public function index(IndexBlogPostsRequest $request): View
    {
        $categories = BlogPostCategory::options();
        $activeCategory = $request->validated('category', 'all');
        $posts = BlogPost::query()->forListing($activeCategory)->paginate(18);

        return view('central.blog.index', [
            'posts' => $posts,
            'categories' => $categories,
            'activeCategory' => $activeCategory,
        ]);
    }

    public function show(BlogPost $centralPost): View
    {
        $related = BlogPost::query()->published()->relatedTo($centralPost)->get();

        return view('central.blog.show', [
            'post' => $centralPost,
            'related' => $related,
        ]);
    }
}
