@extends('blog.layout')

@section('title', 'Changelog — KneadIt')
@section('meta_description', 'See what\'s new in KneadIt. Latest features, improvements, and updates for cottage food bakers.')

@section('styles')
.cl-hero{background:var(--warm-black);padding:3.5rem 1.5rem 2.5rem;text-align:center}
.cl-hero h1{font-family:var(--font-serif);font-size:clamp(1.75rem,4vw,2.5rem);color:var(--cream);margin:0 0 .4rem}
.cl-hero h1 span{color:var(--honey)}
.cl-hero p{color:var(--butter);font-size:.95rem;max-width:480px;margin:.25rem auto 0;line-height:1.6}
.cl-container{max-width:680px;margin:0 auto;padding:2.5rem 1.5rem}
.cl-entry{position:relative;padding-left:2rem;margin-bottom:2.5rem}
.cl-entry::before{content:'';position:absolute;left:0;top:.5rem;width:10px;height:10px;border-radius:50%;background:var(--honey)}
.cl-entry::after{content:'';position:absolute;left:4px;top:1.25rem;width:2px;height:calc(100% + 1rem);background:var(--butter);opacity:.2}
.cl-entry:last-child::after{display:none}
.cl-meta{display:flex;gap:.75rem;align-items:center;margin-bottom:.25rem}
.cl-version{font-size:.7rem;font-weight:700;background:var(--honey);color:var(--white);padding:.15rem .5rem;border-radius:50px}
.cl-date{font-size:.8rem;color:var(--cinnamon)}
.cl-entry h2{font-family:var(--font-serif);font-size:1.2rem;color:var(--warm-black);margin:0 0 .5rem}
.cl-entry ul{list-style:none;padding:0}
.cl-entry li{position:relative;padding-left:1.25rem;font-size:.875rem;color:var(--walnut);line-height:1.7}
.cl-entry li::before{content:'→';position:absolute;left:0;color:var(--honey);font-weight:700}
@endsection

@section('content')
<section class="cl-hero">
    <h1>What's <span>New</span></h1>
    <p>Latest features and improvements to KneadIt.</p>
</section>

<div class="cl-container">
    @foreach($entries as $entry)
    <div class="cl-entry">
        <div class="cl-meta">
            <span class="cl-version">v{{ $entry['version'] }}</span>
            <span class="cl-date">{{ \Carbon\Carbon::parse($entry['date'])->format('M j, Y') }}</span>
        </div>
        <h2>{{ $entry['title'] }}</h2>
        <ul>
            @foreach($entry['items'] as $item)
            <li>{{ $item }}</li>
            @endforeach
        </ul>
    </div>
    @endforeach
</div>
@endsection
