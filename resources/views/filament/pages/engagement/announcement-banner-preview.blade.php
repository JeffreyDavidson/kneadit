@php
    $text = $this->announcement_text ?: 'Your announcement message will appear here...';
    $type = $this->announcement_type ?? 'info';
    $enabled = $this->announcement_enabled;

    $variantClasses = match ($type) {
        'warning' => 'bg-[#fef3cd] text-[#856404] border-[#ffc107]',
        'success' => 'bg-[#d4edda] text-[#155724] border-[#28a745]',
        'holiday' => 'bg-gradient-to-br from-[#c41e3a] to-[#1a6b2a] text-white border-[#ffd700]',
        default => 'bg-[#fff3cd] text-[#664d03] border-[#d4920c]',
    };
@endphp

@if (!$enabled)
    <div class="text-center text-gray-400 dark:text-gray-500 py-4 italic">
        Banner is currently disabled
    </div>
@else
    <div class="relative px-4 py-3 text-center text-sm font-medium rounded-lg border-2 {{ $variantClasses }}">
        <span>{{ $text }}</span>
        <span class="absolute right-3 top-1/2 -translate-y-1/2 opacity-50 text-lg leading-none">&times;</span>
    </div>
@endif
