<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Content\TenantBlogPost;
use Illuminate\Contracts\View\View;

class BlogController extends Controller
{
    public function index(): View
    {
        $posts = TenantBlogPost::query()
            ->published()
            ->latest('published_at')
            ->paginate(6);

        return view('storefront.blog.index', [
            'posts' => $posts,
        ]);
    }

    public function show(TenantBlogPost $post): View
    {
        $relatedPosts = TenantBlogPost::query()
            ->published()
            ->whereKeyNot($post->getKey())
            ->latest('published_at')
            ->limit(3)
            ->get();

        return view('storefront.blog.show', [
            'post' => $post,
            'relatedPosts' => $relatedPosts,
        ]);
    }
}
