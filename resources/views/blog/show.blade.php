@extends('blog.layout')

@section('title', ($post->meta_title ?: $post->title) . ' — KneadIt')
@section('meta_description', $post->meta_description ?: Str::limit(strip_tags($post->body), 155))
@section('og_type', 'article')
@section('canonical', route('blog.show', $post->slug))
@if ($post->featured_image)
@section('og_image', $post->featured_image)
@endif

@section('styles')
.post-hero{background:var(--warm-black);padding:4rem 1.5rem 3rem}
.post-hero .container{max-width:720px}
.post-meta{font-size:.8rem;color:var(--honey);text-transform:uppercase;letter-spacing:.1em;font-weight:600;margin-bottom:.75rem}
.post-hero h1{font-family:var(--font-serif);font-size:clamp(1.75rem,4vw,2.75rem);color:var(--cream);line-height:1.2;margin-bottom:1rem}
.post-hero .excerpt{color:var(--cinnamon);font-size:1.05rem;line-height:1.6}
.post-hero .date{color:var(--cinnamon);font-size:.85rem;margin-top:1rem;opacity:.7}
.post-image{max-width:720px;margin:0 auto;padding:0 1.5rem}
.post-image img{width:100%;border-radius:0 0 12px 12px}
.post-body{padding:2.5rem 1.5rem 4rem}
.related{background:var(--flour);padding:3rem 1.5rem;border-top:1px solid var(--butter)}
.related h2{font-family:var(--font-serif);text-align:center;margin-bottom:2rem;color:var(--warm-black)}
.related-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:1.5rem;max-width:1100px;margin:0 auto}
.related-card{background:var(--white);border-radius:10px;padding:1.25rem;border:1px solid var(--butter);transition:transform .2s}
.related-card:hover{transform:translateY(-2px)}
.related-card-cat{font-size:.7rem;font-weight:700;text-transform:uppercase;color:var(--honey);letter-spacing:.08em}
.related-card-title{font-family:var(--font-serif);font-size:1.05rem;margin:.4rem 0;color:var(--warm-black)}
.related-card-excerpt{font-size:.8rem;color:var(--cinnamon);line-height:1.5}
.back-link{display:inline-flex;align-items:center;gap:.4rem;color:var(--honey);font-size:.875rem;font-weight:600;margin-bottom:1.5rem}
.cta-box{max-width:720px;margin:2rem auto;background:linear-gradient(135deg,var(--warm-black),var(--espresso));border-radius:12px;padding:2rem;text-align:center}
.cta-box h3{font-family:var(--font-serif);color:var(--cream);margin-bottom:.5rem}
.cta-box p{color:var(--cinnamon);font-size:.9rem;margin-bottom:1rem}
.cta-box a{display:inline-block;padding:.6rem 1.5rem;background:var(--honey);color:var(--white);border-radius:50px;font-weight:700;font-size:.9rem}
.post-disclaimer{max-width:720px;margin:2rem auto 0;padding:1rem 1.25rem;border-left:3px solid var(--sourdough);font-size:.78rem;color:var(--cinnamon);line-height:1.6}
.post-disclaimer p{margin:0}
@endsection

@section('content')
<div class="post-hero">
    <div class="container" style="max-width:720px">
        <a href="{{ route('blog.index') }}" class="back-link" style="color:var(--honey)">← Back to Blog</a>
        <div class="post-meta">{{ $post->category }}</div>
        <h1>{{ $post->title }}</h1>
        @if ($post->excerpt)
            <p class="excerpt">{{ $post->excerpt }}</p>
        @endif
        {{-- date removed per Cassie's request --}}
    </div>
</div>

@if ($post->featured_image)
<div class="post-image">
    <img src="{{ $post->featured_image }}" alt="{{ $post->title }}">
</div>
@endif

<div class="post-body">
    <div class="prose">
        {!! clean($post->body) !!}
    </div>

    {{-- CTA --}}
    <div class="cta-box">
        <h3>Ready to manage your bakery like a pro?</h3>
        <p>KneadIt gives cottage food bakers the tools to take orders, manage finances, and grow, all in one place.</p>
        <a href="/register">Start Your Free Trial →</a>
    </div>

    {{-- Disclaimer --}}
    <div class="post-disclaimer">
        <p>This content is for informational purposes only and is not legal advice. Cottage food laws vary by state. You are responsible for understanding and complying with your state's regulations.</p>
    </div>
</div>

@if ($related->count())
<div class="related">
    <h2>Keep Reading</h2>
    <div class="related-grid">
        @foreach ($related as $relatedPost)
            <a href="{{ route('blog.show', $relatedPost->slug) }}" class="related-card">
                <div class="related-card-cat">{{ $relatedPost->category }}</div>
                <div class="related-card-title">{{ $relatedPost->title }}</div>
                <div class="related-card-excerpt">{{ Str::limit($relatedPost->excerpt ?? strip_tags($relatedPost->body), 100) }}</div>
            </a>
        @endforeach
    </div>
</div>
@endif
@endsection
