@props(['title' => null, 'subtitle' => null])

<div {{ $attributes->class(['tenant-admin-card']) }}>
    @if ($title)
        <div data-admin-gradient-header>
            <div>
                <h3 data-header-title>{{ $title }}</h3>
                @if ($subtitle)
                    <p class="mt-1 mb-0 text-xs">{{ $subtitle }}</p>
                @endif
            </div>
        </div>
    @endif
    <div class="px-5 py-4">{{ $slot }}</div>
</div>
