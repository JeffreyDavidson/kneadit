<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\TenantBlogPost;
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
        return view('storefront.blog.show', [
            'post' => $post,
        ]);
    }
}
