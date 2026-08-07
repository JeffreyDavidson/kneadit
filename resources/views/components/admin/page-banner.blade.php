@props(['title'])

<div class="from-brand-900 to-brand-700 mb-6 rounded-2xl bg-gradient-to-br px-7 py-6">
    <h2 class="m-0 text-[1.3rem] font-bold text-white">{{ $title }}</h2>
    @if ($slot->isNotEmpty())
        <div class="mt-2 text-[0.9rem] text-white/80">{{ $slot }}</div>
    @endif
</div>
