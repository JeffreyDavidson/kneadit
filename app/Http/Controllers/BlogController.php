<?php

namespace App\Http\Controllers;

use App\Enums\BlogPostCategory;
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

        $query = BlogPost::query()->published()->orderByDesc('published_at');

        if ($activeCategory !== 'all') {
            $category = BlogPostCategory::tryFrom($activeCategory);
            if ($category) {
                $query->inCategory($category);
            }
        }

        $posts = $query->paginate(18);

        return view('blog.index', compact('posts', 'categories', 'activeCategory'));
    }

    public function show(string $slug): View
    {
        $post = BlogPost::query()->published()->where('slug', $slug)->firstOrFail();

        $related = BlogPost::query()->published()->relatedTo($post)->get();

        return view('blog.show', compact('post', 'related'));
    }
}
