@props(['width' => 'w-[360px]', 'closeAction' => 'closeEditModal'])

<div class="fixed inset-0 bg-black/40 z-50 flex items-center justify-center" wire:click.self="{{ $closeAction }}">
    <div {{ $attributes->class(['bg-white rounded-xl border border-brand-200 p-6 max-w-[90vw]', $width]) }}>
        {{ $slot }}
    </div>
</div>
