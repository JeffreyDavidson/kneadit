@extends('blog.layout')

@section('title', 'Blog — KneadIt | Resources for Cottage Food Bakers')
@section('meta_description', 'Guides, tips, and resources for cottage food bakers. Learn about cottage food laws, pricing, marketing, and growing your home bakery business.')

@section('styles')
<style>
.blog-hero{background:var(--warm-black);padding:4rem 1.5rem 3rem;text-align:center}
.blog-hero h1{font-family:var(--font-serif);font-size:clamp(2rem,4vw,3rem);color:var(--cream);margin-bottom:.5rem}
.blog-hero p{color:var(--cinnamon);font-size:1.1rem;max-width:600px;margin:0 auto}
.categories{display:flex;gap:.5rem;justify-content:center;flex-wrap:wrap;padding:1.5rem;background:var(--flour);border-bottom:1px solid var(--butter)}
.cat-btn{padding:.4rem 1rem;border-radius:50px;font-size:.8rem;font-weight:600;border:1px solid var(--butter);background:var(--white);color:var(--walnut);transition:all .2s;cursor:pointer;text-decoration:none}
.cat-btn:hover,.cat-btn.active{background:var(--honey);color:var(--white);border-color:var(--honey)}
.posts-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:2rem;padding:3rem 0}
.post-card{background:var(--white);border-radius:12px;overflow:hidden;border:1px solid var(--butter);transition:transform .2s,box-shadow .2s}
.post-card:hover{transform:translateY(-4px);box-shadow:0 12px 32px rgba(0,0,0,.08)}
.post-card-img{height:200px;background:var(--espresso);overflow:hidden}
.post-card-img img{width:100%;height:100%;object-fit:cover}
.post-card-body{padding:1.25rem}
.post-card-cat{font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em;color:var(--honey);margin-bottom:.5rem}
.post-card-title{font-family:var(--font-serif);font-size:1.2rem;color:var(--warm-black);margin-bottom:.5rem;line-height:1.3}
.post-card-excerpt{font-size:.875rem;color:var(--cinnamon);line-height:1.6;margin-bottom:.75rem}
.post-card-date{font-size:.75rem;color:var(--cinnamon);opacity:.7}
.empty-state{text-align:center;padding:4rem 1.5rem}
.empty-state h2{font-family:var(--font-serif);color:var(--walnut);margin-bottom:.5rem}
.empty-state p{color:var(--cinnamon)}
</style>
@endsection

@section('content')
<div class="blog-hero">
    <h1>The Baker's Resource</h1>
    <p>Everything you need to start, run, and grow your cottage food bakery business.</p>
</div>

<div class="categories">
    @foreach($categories as $key => $label)
        <a href="{{ $key === 'all' ? route('blog.index') : route('blog.index', ['category' => $key]) }}"
           class="cat-btn {{ $activeCategory === $key ? 'active' : '' }}">
            {{ $label }}
        </a>
    @endforeach
</div>

<div class="container">
    @if($posts->count())
        <div class="posts-grid">
            @foreach($posts as $post)
                <a href="{{ route('blog.show', $post->slug) }}" class="post-card">
                    @if($post->featured_image)
                        <div class="post-card-img">
                            <img src="{{ $post->featured_image }}" alt="{{ $post->title }}">
                        </div>
                    @else
                        <div class="post-card-img" style="display:flex;align-items:center;justify-content:center;">
                            <span style="font-family:var(--font-serif);font-size:2rem;color:var(--walnut);opacity:.3">KneadIt</span>
                        </div>
                    @endif
                    <div class="post-card-body">
                        <div class="post-card-cat">{{ $categories[$post->category] ?? $post->category }}</div>
                        <div class="post-card-title">{{ $post->title }}</div>
                        <div class="post-card-excerpt">{{ Str::limit($post->excerpt ?? strip_tags($post->body), 120) }}</div>
                        <div class="post-card-date">{{ $post->published_at->format('M j, Y') }}</div>
                    </div>
                </a>
            @endforeach
        </div>

        {{ $posts->links() }}
    @else
        <div class="empty-state">
            <h2>Coming Soon</h2>
            <p>We're working on helpful guides and resources for cottage food bakers. Check back soon!</p>
        </div>
    @endif
</div>
@endsection
