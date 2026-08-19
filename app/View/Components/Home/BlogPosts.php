<?php

namespace App\View\Components\Home;

use App\Models\Content\BlogPost;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class BlogPosts extends Component
{
    /** @var Collection<int, BlogPost> */
    public Collection $latestPosts;

    public string $title;

    public string $subtitle;

    /** @param array<string, mixed> $config */
    public function __construct(public array $config = [])
    {
        $count = is_int($config['count'] ?? null) ? $config['count'] : 3;
        $this->title = is_string($config['title'] ?? null) ? $config['title'] : 'From the Kitchen';
        $this->subtitle = is_string($config['subtitle'] ?? null) ? $config['subtitle'] : 'Stories, tips, and updates';

        try {
            $this->latestPosts = BlogPost::query()
                ->where('is_published', true)
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->latest('published_at')
                ->take($count)
                ->get();
        } catch (\Exception) {
            $this->latestPosts = (new BlogPost)->newCollection();
        }
    }

    public function render(): View
    {
        return view('components.home.blog');
    }
}
