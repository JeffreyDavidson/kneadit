@extends('blog.layout')

@section('title', 'Resources — KneadIt | Guides for Cottage Food Bakers')
@section('meta_description', 'Guides, tips, and resources for cottage food bakers. Learn about cottage food laws, pricing, marketing, and growing your home bakery business.')

@section('styles')
<style>
.res-hero{background:var(--warm-black);padding:4rem 1.5rem 2.5rem;text-align:center}
.res-hero h1{font-family:var(--font-serif);font-size:clamp(1.75rem,4vw,2.75rem);color:var(--cream);margin:0 0 .4rem;line-height:1.15}
.res-hero h1 span{color:var(--honey)}
.res-hero p{color:var(--cinnamon);font-size:.95rem;max-width:480px;margin:.25rem auto 0;line-height:1.6;opacity:.8}

.cat-strip{display:flex;gap:.5rem;justify-content:center;flex-wrap:wrap;padding:1.25rem 1.5rem 0;background:var(--warm-black);padding-bottom:2rem;margin-bottom:2.5rem;border-bottom:1px solid rgba(255,255,255,.06)}
.cat-strip a{padding:.45rem 1.1rem;border-radius:50px;font-size:.78rem;font-weight:600;background:transparent;color:var(--cinnamon);border:1px solid rgba(139,104,68,.3);transition:all .2s;text-decoration:none}
.cat-strip a:hover{border-color:var(--honey);color:var(--honey)}
.cat-strip a.on{background:var(--honey);color:var(--white);border-color:var(--honey)}

.container{max-width:1000px;margin:0 auto;padding:0 1.5rem}

/* Featured — first post is large */
.feat{display:block;background:var(--warm-black);border-radius:16px;padding:2.5rem;margin-bottom:2.5rem;border:1px solid rgba(255,255,255,.06);transition:border-color .3s;text-decoration:none}
.feat:hover{border-color:var(--honey)}
.feat-label{font-size:.65rem;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:var(--honey);margin-bottom:.75rem}
.feat h2{font-family:var(--font-serif);font-size:clamp(1.5rem,3vw,2rem);color:var(--cream);margin:0 0 .75rem;line-height:1.25}
.feat p{color:var(--cinnamon);font-size:.95rem;line-height:1.7;margin:0 0 1rem;max-width:640px}
.feat-foot{display:flex;align-items:center;gap:1.5rem;font-size:.8rem}
.feat-foot .date{color:var(--cinnamon)}
.feat-foot .read{color:var(--honey);font-weight:600}

/* Grid */
.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:1.25rem;margin-bottom:3rem}
.card{display:block;background:var(--white);border-radius:12px;padding:1.5rem;border:1px solid var(--butter);transition:transform .2s,box-shadow .2s,border-color .2s;text-decoration:none}
.card:hover{transform:translateY(-4px);box-shadow:0 12px 32px rgba(28,20,16,.08);border-color:var(--honey)}
.card-cat{font-size:.65rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--honey);margin-bottom:.5rem}
.card h3{font-family:var(--font-serif);font-size:1.1rem;color:var(--warm-black);margin:0 0 .5rem;line-height:1.3}
.card p{font-size:.85rem;color:var(--walnut);line-height:1.6;margin:0 0 .75rem}
.card .date{font-size:.72rem;color:var(--cinnamon)}

/* CTA */
.res-cta{background:var(--warm-black);padding:4rem 1.5rem;text-align:center;margin-top:1rem}
.res-cta h2{font-family:var(--font-serif);font-size:clamp(1.5rem,3vw,2rem);color:var(--cream);margin:0 0 .5rem}
.res-cta p{color:var(--cinnamon);max-width:480px;margin:0 auto 1.5rem;font-size:.95rem;line-height:1.7}
.res-cta a{display:inline-block;padding:.75rem 2rem;border-radius:50px;background:var(--honey);color:var(--white);font-weight:700;font-size:.9rem;transition:background .2s;text-decoration:none}
.res-cta a:hover{background:var(--golden)}

.empty{text-align:center;padding:4rem 0}
.empty h2{font-family:var(--font-serif);color:var(--walnut)}
.empty p{color:var(--cinnamon)}

@media(max-width:600px){
    .grid{grid-template-columns:1fr}
    .feat{padding:1.75rem}
}
</style>
@endsection

@section('content')
<section class="res-hero">
    <h1>Baker's <span>Resources</span></h1>
    <p>Everything you need to start, run, and grow your cottage food business.</p>
</section>

<div class="cat-strip">
    @foreach($categories as $key => $label)
        <a href="{{ $key === 'all' ? route('blog.index') : route('blog.index', ['category' => $key]) }}"
           class="{{ $activeCategory === $key ? 'on' : '' }}">{{ $label }}</a>
    @endforeach
</div>

<div class="container">
    @if($posts->count())
        {{-- Featured post (first on page 1) --}}
        @if($posts->currentPage() === 1)
            @php $featured = $posts->first(); @endphp
            <a href="{{ route('blog.show', $featured->slug) }}" class="feat">
                <div class="feat-label">{{ $categories[$featured->category] ?? $featured->category }}</div>
                <h2>{{ $featured->title }}</h2>
                <p>{{ Str::limit($featured->excerpt ?? strip_tags($featured->body), 180) }}</p>
                <div class="feat-foot">
                    <span class="date">{{ $featured->published_at->format('M j, Y') }}</span>
                    <span class="read">Read →</span>
                </div>
            </a>
        @endif

        {{-- Grid --}}
        <div class="grid">
            @foreach($posts as $i => $post)
                @if($posts->currentPage() === 1 && $i === 0) @continue @endif
                <a href="{{ route('blog.show', $post->slug) }}" class="card">
                    <div class="card-cat">{{ $categories[$post->category] ?? $post->category }}</div>
                    <h3>{{ $post->title }}</h3>
                    <p>{{ Str::limit($post->excerpt ?? strip_tags($post->body), 110) }}</p>
                    <div class="date">{{ $post->published_at->format('M j, Y') }}</div>
                </a>
            @endforeach
        </div>

        {{ $posts->links() }}
    @else
        <div class="empty">
            <h2>Coming Soon</h2>
            <p>We're working on helpful resources for cottage food bakers.</p>
        </div>
    @endif
</div>

<section class="res-cta">
    <h2>Ready to manage your bakery?</h2>
    <p>KneadIt gives cottage food bakers the tools to take orders, manage finances, and grow.</p>
    <a href="/register">Start Your Free Trial →</a>
</section>
@endsection
