@extends('blog.layout')

@section('title', 'Blog — KneadIt | Resources for Cottage Food Bakers')
@section('meta_description', 'Guides, tips, and resources for cottage food bakers. Learn about cottage food laws, pricing, marketing, and growing your home bakery business.')

@section('styles')
<style>
/* ===== HERO ===== */
.blog-hero{position:relative;background:var(--warm-black);padding:6rem 1.5rem 4rem;text-align:center;overflow:hidden}
.blog-hero::before{content:'';position:absolute;inset:0;background:radial-gradient(ellipse at 50% 0%,rgba(212,146,12,.08) 0%,transparent 70%);pointer-events:none}
.blog-hero-content{position:relative;z-index:1}
.blog-hero-eyebrow{font-size:.8rem;letter-spacing:.3em;text-transform:uppercase;color:var(--honey);margin-bottom:1rem;font-weight:600}
.blog-hero h1{font-family:var(--font-serif);font-size:clamp(2.5rem,6vw,4rem);color:var(--cream);margin-bottom:.75rem;line-height:1.1}
.blog-hero-sub{color:var(--cinnamon);font-size:1.15rem;max-width:540px;margin:0 auto;line-height:1.7;opacity:0;animation:hero-fade-in .8s .3s forwards}
@keyframes hero-fade-in{to{opacity:1}}

/* Flour particles */
.flour-wrap{position:absolute;inset:0;overflow:hidden;pointer-events:none;z-index:0}
.flour-particle{position:absolute;border-radius:50%;background:var(--cream);opacity:.15;animation:flour-drift linear infinite}
@keyframes flour-drift{0%{transform:translateY(-10px) translateX(0);opacity:0}10%{opacity:.15}90%{opacity:.15}100%{transform:translateY(100%) translateX(var(--sway,20px));opacity:0}}

/* ===== FEATURED POST ===== */
.featured-post{display:block;position:relative;max-width:1100px;margin:-2rem auto 0;padding:2.5rem;background:linear-gradient(135deg,var(--espresso),var(--warm-black));border:1px solid rgba(232,176,74,.15);border-radius:16px;z-index:2;transition:border-color .3s,box-shadow .3s}
.featured-post:hover{border-color:rgba(232,176,74,.4);box-shadow:0 8px 40px rgba(0,0,0,.3)}
.featured-badge{display:inline-block;font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.15em;color:var(--warm-black);background:var(--golden);padding:.3rem .8rem;border-radius:50px;margin-bottom:1rem}
.featured-cat{font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.12em;color:var(--honey);margin-bottom:.5rem;display:inline-block;margin-left:.75rem}
.featured-title{font-family:var(--font-serif);font-size:clamp(1.5rem,3vw,2.25rem);color:var(--cream);line-height:1.25;margin-bottom:.75rem}
.featured-excerpt{color:var(--cinnamon);font-size:1rem;line-height:1.7;max-width:700px;margin-bottom:1rem}
.featured-meta{display:flex;align-items:center;gap:1rem;font-size:.8rem;color:var(--cinnamon);opacity:.7}
.featured-read{color:var(--honey);font-weight:600;font-size:.85rem;display:inline-flex;align-items:center;gap:.4rem;transition:gap .2s}
.featured-post:hover .featured-read{gap:.7rem}

/* ===== CATEGORIES ===== */
.cat-bar{background:var(--espresso);border-top:1px solid rgba(255,255,255,.05);border-bottom:1px solid rgba(255,255,255,.05);padding:1.25rem 1.5rem;position:sticky;top:64px;z-index:10}
.cat-bar-inner{display:flex;gap:.6rem;justify-content:center;flex-wrap:wrap;max-width:1100px;margin:0 auto}
.cat-pill{padding:.45rem 1.1rem;border-radius:50px;font-size:.78rem;font-weight:600;border:1px solid var(--walnut);background:transparent;color:var(--cinnamon);transition:all .25s;cursor:pointer;text-decoration:none;white-space:nowrap}
.cat-pill:hover{color:var(--cream);border-color:var(--cinnamon)}
.cat-pill.active{background:var(--honey);color:var(--white);border-color:var(--honey)}

/* ===== POST GRID ===== */
.posts-section{max-width:1100px;margin:0 auto;padding:3rem 1.5rem 4rem}
.posts-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:1.5rem}
.post-card{display:block;background:var(--warm-black);border-radius:14px;overflow:hidden;border:1px solid rgba(255,255,255,.06);transition:transform .3s,border-color .3s,box-shadow .3s;text-decoration:none}
.post-card:hover{transform:translateY(-6px);border-color:rgba(232,176,74,.3);box-shadow:0 16px 48px rgba(0,0,0,.15)}

/* Category icon area */
.post-card-icon{height:160px;display:flex;align-items:center;justify-content:center;position:relative;overflow:hidden}
.post-card-icon::before{content:'';position:absolute;inset:0;opacity:.08}
.post-card-icon svg{width:56px;height:56px;opacity:.6;transition:opacity .3s,transform .3s}
.post-card:hover .post-card-icon svg{opacity:.9;transform:scale(1.1)}

/* Category-specific colors */
.post-card[data-cat="getting-started"] .post-card-icon{background:linear-gradient(135deg,rgba(90,122,90,.15),rgba(90,122,90,.05))}
.post-card[data-cat="getting-started"] .post-card-icon svg{color:var(--sage)}
.post-card[data-cat="cottage-food-laws"] .post-card-icon{background:linear-gradient(135deg,rgba(168,50,72,.15),rgba(168,50,72,.05))}
.post-card[data-cat="cottage-food-laws"] .post-card-icon svg{color:var(--berry)}
.post-card[data-cat="pricing"] .post-card-icon{background:linear-gradient(135deg,rgba(212,146,12,.15),rgba(212,146,12,.05))}
.post-card[data-cat="pricing"] .post-card-icon svg{color:var(--honey)}
.post-card[data-cat="marketing"] .post-card-icon{background:linear-gradient(135deg,rgba(196,149,106,.15),rgba(196,149,106,.05))}
.post-card[data-cat="marketing"] .post-card-icon svg{color:var(--crust)}
.post-card[data-cat="operations"] .post-card-icon{background:linear-gradient(135deg,rgba(139,104,68,.15),rgba(139,104,68,.05))}
.post-card[data-cat="operations"] .post-card-icon svg{color:var(--cinnamon)}
.post-card[data-cat="recipes-tips"] .post-card-icon{background:linear-gradient(135deg,rgba(232,176,74,.15),rgba(232,176,74,.05))}
.post-card[data-cat="recipes-tips"] .post-card-icon svg{color:var(--golden)}
/* Default */
.post-card-icon{background:linear-gradient(135deg,rgba(139,104,68,.12),rgba(139,104,68,.04))}
.post-card-icon svg{color:var(--cinnamon)}

.post-card-body{padding:1.5rem}
.post-card-cat{font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.12em;color:var(--honey);margin-bottom:.6rem}
.post-card-title{font-family:var(--font-serif);font-size:1.15rem;color:var(--cream);margin-bottom:.6rem;line-height:1.35}
.post-card-excerpt{font-size:.85rem;color:var(--cinnamon);line-height:1.65;margin-bottom:.75rem}
.post-card-date{font-size:.72rem;color:var(--cinnamon);opacity:.5}

/* ===== PAGINATION ===== */
.pagination-wrap{display:flex;justify-content:center;padding:2rem 0 0}
.pagination-wrap nav{display:flex;gap:.4rem}
.pagination-wrap .page-link,.pagination-wrap nav a,.pagination-wrap nav span{padding:.5rem 1rem;border-radius:8px;font-size:.85rem;font-weight:600;border:1px solid var(--butter);background:var(--white);color:var(--walnut);transition:all .2s;text-decoration:none}
.pagination-wrap nav a:hover{background:var(--honey);color:var(--white);border-color:var(--honey)}
.pagination-wrap nav span.current,.pagination-wrap .page-item.active .page-link{background:var(--honey);color:var(--white);border-color:var(--honey)}

/* ===== EMPTY STATE ===== */
.empty-state{text-align:center;padding:5rem 1.5rem}
.empty-state h2{font-family:var(--font-serif);color:var(--walnut);margin-bottom:.5rem;font-size:1.75rem}
.empty-state p{color:var(--cinnamon);font-size:1rem}

/* ===== BOTTOM CTA ===== */
.blog-cta{background:var(--warm-black);padding:5rem 1.5rem;text-align:center;position:relative;overflow:hidden}
.blog-cta::before{content:'';position:absolute;inset:0;background:radial-gradient(ellipse at 50% 100%,rgba(212,146,12,.06) 0%,transparent 60%);pointer-events:none}
.blog-cta-inner{position:relative;z-index:1;max-width:600px;margin:0 auto}
.blog-cta h2{font-family:var(--font-serif);font-size:clamp(1.75rem,4vw,2.5rem);color:var(--cream);margin-bottom:.75rem;line-height:1.2}
.blog-cta p{color:var(--cinnamon);font-size:1.05rem;line-height:1.7;margin-bottom:2rem}
.blog-cta-btn{display:inline-block;padding:.85rem 2.5rem;border-radius:50px;background:var(--honey);color:var(--white);font-weight:700;font-size:1rem;transition:background .2s,transform .2s;text-decoration:none}
.blog-cta-btn:hover{background:var(--golden);transform:translateY(-2px)}

/* ===== SCROLL REVEAL ===== */
.reveal{opacity:0;transform:translateY(30px);transition:opacity .6s ease,transform .6s ease}
.reveal.visible{opacity:1;transform:translateY(0)}

/* ===== RESPONSIVE ===== */
@media(max-width:600px){
    .blog-hero{padding:4.5rem 1.25rem 3rem}
    .featured-post{margin:0 1rem;padding:1.75rem}
    .posts-grid{grid-template-columns:1fr}
    .cat-bar{top:0}
}
@media(prefers-reduced-motion:reduce){
    .reveal{opacity:1;transform:none;transition:none}
    .flour-particle{display:none!important}
    .blog-hero-sub{opacity:1;animation:none}
}
</style>
@endsection

@section('content')
{{-- HERO --}}
<section class="blog-hero">
    <div class="flour-wrap" id="flour"></div>
    <div class="blog-hero-content">
        <div class="blog-hero-eyebrow">Resources & Guides</div>
        <h1>The Baker's Resource</h1>
        <p class="blog-hero-sub">Everything you need to start, run, and grow your cottage food bakery business.</p>
    </div>
</section>

{{-- FEATURED POST --}}
@if($posts->count() && $posts->currentPage() === 1)
    @php $featured = $posts->first(); @endphp
    <div class="container">
        <a href="{{ route('blog.show', $featured->slug) }}" class="featured-post reveal">
            <span class="featured-badge">✦ Featured</span>
            <span class="featured-cat">{{ $categories[$featured->category] ?? $featured->category }}</span>
            <div class="featured-title">{{ $featured->title }}</div>
            <div class="featured-excerpt">{{ Str::limit($featured->excerpt ?? strip_tags($featured->body), 200) }}</div>
            <div class="featured-meta">
                <span>{{ $featured->published_at->format('M j, Y') }}</span>
                <span class="featured-read">Read article →</span>
            </div>
        </a>
    </div>
@endif

{{-- CATEGORIES --}}
<div class="cat-bar">
    <div class="cat-bar-inner">
        @foreach($categories as $key => $label)
            <a href="{{ $key === 'all' ? route('blog.index') : route('blog.index', ['category' => $key]) }}"
               class="cat-pill {{ $activeCategory === $key ? 'active' : '' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>
</div>

{{-- POSTS --}}
<div class="posts-section">
    @if($posts->count())
        <div class="posts-grid">
            @foreach($posts as $index => $post)
                @if($posts->currentPage() === 1 && $index === 0) @continue @endif
                <a href="{{ route('blog.show', $post->slug) }}" class="post-card reveal" data-cat="{{ $post->category }}">
                    <div class="post-card-icon">
                        @switch($post->category)
                            @case('getting-started')
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 0 0 1.5-.189m-1.5.189a6.01 6.01 0 0 1-1.5-.189m3.75 7.478a12.06 12.06 0 0 1-4.5 0m3.75 2.383a14.406 14.406 0 0 1-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 1 0-7.517 0c.85.493 1.509 1.333 1.509 2.316V18" /></svg>
                                @break
                            @case('cottage-food-laws')
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21" /></svg>
                                @break
                            @case('pricing')
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                                @break
                            @case('marketing')
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 1 1 0-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 0 1-1.44-4.282m3.102.069a18.03 18.03 0 0 1-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 0 1 8.835 2.535M10.34 6.66a23.847 23.847 0 0 0 8.835-2.535m0 0A23.74 23.74 0 0 0 18.795 3m.38 1.125a23.91 23.91 0 0 1 1.014 5.395m-1.014 8.855c-.118.38-.245.754-.38 1.125m.38-1.125a23.91 23.91 0 0 0 1.014-5.395m0-3.46c.495.413.811 1.035.811 1.73 0 .695-.316 1.317-.811 1.73m0-3.46a24.347 24.347 0 0 1 0 3.46" /></svg>
                                @break
                            @case('operations')
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                @break
                            @case('recipes-tips')
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.867 8.21 8.21 0 0 0 3 2.48Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 18a3.75 3.75 0 0 0 .495-7.468 5.99 5.99 0 0 0-1.925 3.547 5.975 5.975 0 0 1-2.133-1.001A3.75 3.75 0 0 0 12 18Z" /></svg>
                                @break
                            @default
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" /></svg>
                        @endswitch
                    </div>
                    <div class="post-card-body">
                        <div class="post-card-cat">{{ $categories[$post->category] ?? $post->category }}</div>
                        <div class="post-card-title">{{ $post->title }}</div>
                        <div class="post-card-excerpt">{{ Str::limit($post->excerpt ?? strip_tags($post->body), 120) }}</div>
                        <div class="post-card-date">{{ $post->published_at->format('M j, Y') }}</div>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="pagination-wrap">
            {{ $posts->links() }}
        </div>
    @else
        <div class="empty-state">
            <h2>Coming Soon</h2>
            <p>We're working on helpful guides and resources for cottage food bakers. Check back soon!</p>
        </div>
    @endif
</div>

{{-- BOTTOM CTA --}}
<section class="blog-cta">
    <div class="blog-cta-inner reveal">
        <h2>Start Your Bakery Journey</h2>
        <p>Join thousands of cottage food bakers using KneadIt to manage orders, track finances, and grow their business.</p>
        <a href="/register" class="blog-cta-btn">Get Started Free →</a>
    </div>
</section>

<script>
// Flour particles
(function(){
    const wrap=document.getElementById('flour');
    if(!wrap||window.matchMedia('(prefers-reduced-motion:reduce)').matches)return;
    for(let i=0;i<20;i++){
        const p=document.createElement('div');
        p.className='flour-particle';
        const s=Math.random()*4+2;
        p.style.cssText=`width:${s}px;height:${s}px;left:${Math.random()*100}%;top:${Math.random()*100}%;--sway:${(Math.random()-.5)*60}px;animation-duration:${Math.random()*8+6}s;animation-delay:${Math.random()*6}s`;
        wrap.appendChild(p);
    }
})();

// Scroll reveal
(function(){
    const els=document.querySelectorAll('.reveal');
    if(!els.length)return;
    const io=new IntersectionObserver((entries)=>{
        entries.forEach(e=>{if(e.isIntersecting){e.target.classList.add('visible');io.unobserve(e.target)}});
    },{threshold:.15});
    els.forEach(el=>io.observe(el));
})();
</script>
@endsection
