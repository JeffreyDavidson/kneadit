{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
<channel>
    <title>KneadIt — Resources for Cottage Food Bakers</title>
    <link>{{ url('/resources') }}</link>
    <description>Guides, tips, and resources for cottage food bakers.</description>
    <language>en-us</language>
    <atom:link href="{{ url('/resources/feed.xml') }}" rel="self" type="application/rss+xml"/>
    @foreach ($posts as $post)
    <item>
        <title>{{ htmlspecialchars($post->title) }}</title>
        <link>{{ url("/resources/{$post->slug}") }}</link>
        <guid isPermaLink="true">{{ url("/resources/{$post->slug}") }}</guid>
        <description>{{ htmlspecialchars($post->excerpt ?? strip_tags(substr($post->body, 0, 300))) }}</description>
        <pubDate>{{ $post->published_at?->toRfc2822String() }}</pubDate>
        <category>{{ htmlspecialchars($post->category) }}</category>
    </item>
    @endforeach
</channel>
</rss>
