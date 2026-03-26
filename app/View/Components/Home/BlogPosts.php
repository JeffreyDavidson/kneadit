<?php

namespace App\View\Components\Home;

use App\Models\BlogPost;
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
        $count = $config['count'] ?? 3;
        $this->title = $config['title'] ?? 'From the Kitchen';
        $this->subtitle = $config['subtitle'] ?? 'Stories, tips, and updates';

        try {
            $this->latestPosts = BlogPost::query()
                ->where('is_published', true)
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->latest('published_at')
                ->take($count)
                ->get();
        } catch (\Exception) {
            $this->latestPosts = collect();
        }
    }

    public function render(): View
    {
        return view('components.home.blog');
    }
}
