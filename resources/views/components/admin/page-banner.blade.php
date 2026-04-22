@props(['title'])

<div class="rounded-2xl px-7 py-6 mb-6 bg-gradient-to-br from-brand-900 to-brand-700">
    <h2 class="text-white text-[1.3rem] font-bold m-0">{{ $title }}</h2>
    @if ($slot->isNotEmpty())
        <div class="mt-2 text-[0.9rem] text-white/80">{{ $slot }}</div>
    @endif
</div>
