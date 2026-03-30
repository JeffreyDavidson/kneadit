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
        $categories = collect(BlogPostCategory::cases())
            ->mapWithKeys(fn (BlogPostCategory $case) => [$case->value => $case->getLabel()])
            ->prepend('All Posts', 'all');

        $activeCategory = request('category', 'all');

        $query = BlogPost::query()->published()->latest('published_at');

        if ($activeCategory !== 'all') {
            $category = BlogPostCategory::tryFrom($activeCategory);
            if ($category) {
                $query->inCategory($category);
            }
        }

        $posts = $query->paginate(6);

        return view('blog.index', compact('posts', 'categories', 'activeCategory'));
    }

    public function show(BlogPost $post): View
    {
        $related = BlogPost::query()->published()->relatedTo($post)->get();

        return view('blog.show', compact('post', 'related'));
    }
}
