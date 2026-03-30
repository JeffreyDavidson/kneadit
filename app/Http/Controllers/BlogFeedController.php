<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use Illuminate\Http\Response;

class BlogFeedController extends Controller
{
    /**
     * Generate the RSS feed for the central blog.
     */
    public function __invoke(): Response
    {
        $posts = BlogPost::published()
            ->orderByDesc('published_at')
            ->take(20)
            ->get();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">';
        $xml .= '<channel>';
        $xml .= '<title>KneadIt — Resources for Cottage Food Bakers</title>';
        $xml .= '<link>' . url('/resources') . '</link>';
        $xml .= '<description>Guides, tips, and resources for cottage food bakers.</description>';
        $xml .= '<language>en-us</language>';
        $xml .= '<atom:link href="' . url('/resources/feed.xml') . '" rel="self" type="application/rss+xml"/>';

        foreach ($posts as $post) {
            $xml .= '<item>';
            $xml .= '<title>' . htmlspecialchars($post->title) . '</title>';
            $xml .= '<link>' . url("/resources/{$post->slug}") . '</link>';
            $xml .= '<guid isPermaLink="true">' . url("/resources/{$post->slug}") . '</guid>';
            $xml .= '<description>' . htmlspecialchars($post->excerpt ?? strip_tags(substr($post->body, 0, 300))) . '</description>';
            $xml .= '<pubDate>' . $post->published_at?->toRfc2822String() . '</pubDate>';
            $xml .= '<category>' . htmlspecialchars($post->category) . '</category>';
            $xml .= '</item>';
        }

        $xml .= '</channel></rss>';

        return response($xml, 200, [
            'Content-Type' => 'application/rss+xml; charset=UTF-8',
        ]);
    }
}
