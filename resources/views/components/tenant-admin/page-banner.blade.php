@props(['title'])

<div class="tenant-admin-card mb-6 px-7 py-6">
    <h2 class="text-brand-50 m-0 text-[1.3rem] font-bold">{{ $title }}</h2>
    @if ($slot->isNotEmpty())
        <div class="text-brand-200 mt-2 text-[0.9rem]">{{ $slot }}</div>
    @endif
</div>
