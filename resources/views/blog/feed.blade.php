{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <title>{{ $storeName }} Blog</title>
        <link>{{ url('/blog') }}</link>
        <description>Latest updates from {{ $storeName }}</description>
        <atom:link href="{{ route('storefront.blog.feed') }}" rel="self" type="application/rss+xml"/>
        <language>en-us</language>
        @foreach($posts as $post)
        <item>
            <title>{{ htmlspecialchars($post->title, ENT_XML1) }}</title>
            <link>{{ route('storefront.blog.show', $post->slug) }}</link>
            <guid>{{ route('storefront.blog.show', $post->slug) }}</guid>
            <pubDate>{{ $post->published_at->toRfc2822String() }}</pubDate>
            @if($post->excerpt)
            <description>{{ htmlspecialchars($post->excerpt, ENT_XML1) }}</description>
            @endif
            @if($post->author_name)
            <author>{{ htmlspecialchars($post->author_name, ENT_XML1) }}</author>
            @endif
        </item>
        @endforeach
    </channel>
</rss>
