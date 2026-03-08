@php
    $text = $this->announcement_text ?: 'Your announcement message will appear here...';
    $type = $this->announcement_type ?? 'info';
    $enabled = $this->announcement_enabled;
@endphp

@if(!$enabled)
    <div class="text-center text-gray-400 dark:text-gray-500 py-4 italic">
        Banner is currently disabled
    </div>
@else
    <div class="relative px-4 py-3 text-center text-sm font-medium rounded-lg"
         style="
            @if($type === 'warning')
                background: #fef3cd; color: #856404; border: 2px solid #ffc107;
            @elseif($type === 'success')
                background: #d4edda; color: #155724; border: 2px solid #28a745;
            @elseif($type === 'holiday')
                background: linear-gradient(135deg, #c41e3a, #1a6b2a); color: #fff; border: 2px solid #ffd700;
            @else
                background: #fff3cd; color: #664d03; border: 2px solid #d4920c;
            @endif
         ">
        <span>{{ $text }}</span>
        <span class="absolute right-3 top-1/2 -translate-y-1/2 opacity-50 text-lg leading-none">&times;</span>
    </div>
@endif
