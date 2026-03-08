@extends('layouts.storefront')

@section('content')
<style>
    .track-input {
        width: 100%;
        padding: 1rem 1.25rem;
        border-radius: 0.75rem;
        border: 1.5px solid var(--warm-300);
        background: var(--warm-50);
        font-family: var(--font-body);
        font-size: 1.125rem;
        color: var(--warm-900);
        transition: border-color 0.2s, box-shadow 0.2s;
        outline: none;
    }
    .track-input:focus {
        border-color: var(--warm-500);
        box-shadow: 0 0 0 3px color-mix(in srgb, var(--warm-500) 15%, transparent);
    }
    .track-input::placeholder {
        color: var(--warm-400);
    }
    .track-stepper-dot {
        transition: all 0.3s ease;
    }
    .track-message-baker {
        background: var(--warm-100);
        border-radius: 0.75rem 0.75rem 0.75rem 0.25rem;
    }
    .track-message-customer {
        background: var(--warm-800);
        color: white;
        border-radius: 0.75rem 0.75rem 0.25rem 0.75rem;
    }
</style>

{{-- Hero --}}
<div class="py-20 md:py-24 text-center" style="background: var(--warm-100);">
    <div class="max-w-3xl mx-auto px-4">
        <h1 class="font-display text-5xl md:text-6xl font-bold tracking-tight mb-6" style="color: var(--warm-900);">
            Track Your Order
        </h1>
        <p class="text-lg" style="color: var(--warm-600);">
            Enter your email to see how your order is coming along.
        </p>
    </div>
</div>

<div class="max-w-4xl mx-auto px-4 py-12 md:py-16">

    {{-- Email lookup --}}
    <div class="max-w-lg mx-auto mb-16">
        <form method="POST" action="{{ route('order.track.lookup') }}" class="space-y-4">
            @csrf
            <label for="email" class="block text-sm font-medium mb-1" style="color: var(--warm-700);">Email Address</label>
            <div class="flex gap-3">
                <input type="email" name="email" id="email" class="track-input flex-1"
                       placeholder="you@example.com" value="{{ old('email', $email ?? '') }}" required>
                <button type="submit" class="btn-primary px-6 py-3 rounded-xl text-base flex-shrink-0" style="font-family: var(--font-display);">
                    Look Up
                </button>
            </div>
            @error('email')
                <p class="text-red-600 text-sm">{{ $message }}</p>
            @enderror
        </form>
    </div>

    @isset($orders)
        @if($orders->isEmpty())
        {{-- Empty state --}}
        <div class="text-center py-12">
            <p class="font-display text-2xl md:text-3xl font-bold mb-3" style="color: var(--warm-800);">No orders found</p>
            <p class="text-lg" style="color: var(--warm-600);">
                We couldn't find any orders for <strong>{{ $email }}</strong>.
            </p>
            <p class="mt-2" style="color: var(--warm-500);">Make sure you're using the same email you ordered with.</p>
        </div>
        @else
        <div class="flex items-center gap-4 mb-8">
            <h2 class="font-display text-xl font-bold whitespace-nowrap" style="color: var(--warm-900);">
                Orders for {{ $email }}
            </h2>
            <div class="flex-1 h-px" style="background: var(--warm-300);"></div>
        </div>

        @php
            $allStatuses = ['pending', 'confirmed', 'baking', 'ready', 'delivered'];
            $statusLabels = [
                'pending' => 'Pending',
                'confirmed' => 'Confirmed',
                'baking' => 'Baking',
                'ready' => 'Ready',
                'delivered' => 'Delivered',
            ];
            $statusEmoji = [
                'pending' => '📋',
                'confirmed' => '✅',
                'baking' => '🔥',
                'ready' => '✨',
                'delivered' => '🎉',
            ];
        @endphp

        <div class="space-y-8">
            @foreach($orders as $order)
                @php
                    $isCancelled = $order->status === 'cancelled';
                    $currentIndex = array_search($order->status, $allStatuses);
                    if ($currentIndex === false) $currentIndex = -1;
                @endphp
                <div class="rounded-2xl border overflow-hidden" style="border-color: var(--warm-200); background: white;">
                    {{-- Order header --}}
                    <div class="px-6 md:px-8 py-6 flex flex-wrap items-start justify-between gap-4" style="background: var(--warm-50);">
                        <div>
                            <h3 class="font-display text-xl font-bold" style="color: var(--warm-900);">
                                {{ $statusEmoji[$order->status] ?? '📦' }} Order {{ $order->order_number }}
                            </h3>
                            <p class="text-sm mt-1" style="color: var(--warm-500);">
                                Placed {{ $order->created_at->format('M j, Y \a\t g:i A') }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="font-display text-2xl font-bold" style="color: var(--warm-900);">${{ number_format($order->total, 2) }}</p>
                            @if($isCancelled)
                                <span class="inline-block mt-1 px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">Cancelled</span>
                            @endif
                        </div>
                    </div>

                    <div class="px-6 md:px-8 py-6 space-y-6">
                        {{-- Progress stepper --}}
                        @unless($isCancelled)
                        <div>
                            {{-- Desktop stepper --}}
                            <div class="hidden sm:block">
                                <div class="flex items-center justify-between relative">
                                    <div class="absolute top-4 left-0 right-0 h-1 rounded-full" style="background: var(--warm-200);"></div>
                                    @if($currentIndex > 0)
                                    <div class="absolute top-4 left-0 h-1 rounded-full transition-all duration-500" style="background: var(--warm-500); width: {{ ($currentIndex / (count($allStatuses) - 1)) * 100 }}%;"></div>
                                    @endif

                                    @foreach($allStatuses as $i => $step)
                                        @php $isCompleted = $i <= $currentIndex; @endphp
                                        <div class="relative z-10 flex flex-col items-center" style="width: {{ 100 / count($allStatuses) }}%;">
                                            <div class="track-stepper-dot w-9 h-9 rounded-full flex items-center justify-center text-sm font-semibold {{ $isCompleted && $i === $currentIndex ? 'ring-4' : '' }}"
                                                 style="background: {{ $isCompleted ? 'var(--warm-500)' : 'var(--warm-200)' }}; color: {{ $isCompleted ? 'white' : 'var(--warm-500)' }}; {{ $isCompleted && $i === $currentIndex ? 'ring-color: color-mix(in srgb, var(--warm-500) 20%, transparent);' : '' }}">
                                                @if($isCompleted && $i < $currentIndex)
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                                @else
                                                    {{ $i + 1 }}
                                                @endif
                                            </div>
                                            <span class="mt-2 text-xs font-medium text-center" style="color: {{ $isCompleted ? 'var(--warm-700)' : 'var(--warm-400)' }};">
                                                {{ $statusLabels[$step] }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Mobile stepper --}}
                            <div class="sm:hidden space-y-3">
                                @foreach($allStatuses as $i => $step)
                                    @php $isCompleted = $i <= $currentIndex; @endphp
                                    <div class="flex items-center gap-3">
                                        <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-semibold flex-shrink-0"
                                             style="background: {{ $isCompleted ? 'var(--warm-500)' : 'var(--warm-200)' }}; color: {{ $isCompleted ? 'white' : 'var(--warm-500)' }};">
                                            @if($isCompleted && $i < $currentIndex)
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                            @else
                                                {{ $i + 1 }}
                                            @endif
                                        </div>
                                        <span class="text-sm font-medium" style="color: {{ $isCompleted ? 'var(--warm-900)' : 'var(--warm-400)' }};">
                                            {{ $statusLabels[$step] }}
                                        </span>
                                    </div>
                                    @if($i < count($allStatuses) - 1)
                                        <div class="ml-3 w-0.5 h-3" style="background: {{ $i < $currentIndex ? 'var(--warm-500)' : 'var(--warm-200)' }};"></div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                        @endunless

                        {{-- Reorder --}}
                        <div class="border-t pt-4" style="border-color: var(--warm-200);">
                            <a href="{{ route('order.create') }}?reorder={{ $order->id }}"
                               class="inline-flex items-center gap-2 text-sm font-medium px-4 py-2 rounded-lg transition-colors"
                               style="background: var(--warm-100); color: var(--warm-700);"
                               onmouseover="this.style.background='var(--warm-200)'"
                               onmouseout="this.style.background='var(--warm-100)'">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                Order Again
                            </a>
                        </div>

                        {{-- Items --}}
                        <div class="border-t pt-4" style="border-color: var(--warm-200);">
                            <h4 class="text-sm font-medium mb-3" style="color: var(--warm-500);">Items Ordered</h4>
                            <div class="space-y-2">
                                @foreach($order->orderItems as $item)
                                    <div class="flex justify-between text-sm">
                                        <span style="color: var(--warm-800);">
                                            {{ $item->product->name ?? 'Product' }}
                                            <span style="color: var(--warm-500);">× {{ $item->quantity }}</span>
                                        </span>
                                        <span class="font-medium" style="color: var(--warm-800);">${{ number_format($item->total_price, 2) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Messages --}}
                        <div class="border-t pt-4" style="border-color: var(--warm-200);">
                            <h4 class="text-sm font-medium mb-3 flex items-center gap-2" style="color: var(--warm-500);">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                Messages
                            </h4>
                            <div id="messages-{{ $order->id }}" class="space-y-3 mb-4 max-h-64 overflow-y-auto">
                                <p class="text-sm italic" style="color: var(--warm-400);">Loading messages...</p>
                            </div>
                            <form onsubmit="sendOrderMessage(event, {{ $order->id }})" class="flex gap-2">
                                <input type="text" id="msg-input-{{ $order->id }}" placeholder="Type a message..." class="input-field flex-1 text-sm" required>
                                <button type="submit" class="btn-primary text-sm px-4 py-2">Send</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        @endif
    @endisset
</div>

@isset($orders)
@if($orders->isNotEmpty())
<script>
const customerEmail = @json($email);
const customerName = @json($orders->first()->customer->name ?? $email);

function renderMessages(orderId, messages) {
    const container = document.getElementById('messages-' + orderId);
    if (!messages.length) {
        container.innerHTML = '<p class="text-sm italic" style="color: var(--warm-400);">No messages yet. Say hello!</p>';
        return;
    }
    container.innerHTML = messages.map(msg => {
        const isBaker = msg.sender_type === 'baker';
        const cls = isBaker ? 'track-message-baker' : 'track-message-customer';
        const align = isBaker ? 'items-start' : 'items-end';
        const time = new Date(msg.created_at).toLocaleString([], {month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'});
        return `<div class="flex flex-col ${align}">
            <div class="${cls} px-4 py-3 max-w-xs text-sm">
                <p class="font-medium text-xs mb-1 opacity-70">${msg.sender_name}</p>
                <p class="whitespace-pre-wrap">${msg.message.replace(/</g,'&lt;')}</p>
            </div>
            <span class="text-xs mt-1" style="color: var(--warm-400);">${time}</span>
        </div>`;
    }).join('');
    container.scrollTop = container.scrollHeight;
}

function loadMessages(orderId) {
    fetch('/order/' + orderId + '/messages')
        .then(r => r.json())
        .then(data => renderMessages(orderId, data.messages))
        .catch(() => {
            document.getElementById('messages-' + orderId).innerHTML = '<p class="text-sm italic" style="color: var(--warm-400);">Could not load messages.</p>';
        });
}

function sendOrderMessage(e, orderId) {
    e.preventDefault();
    const input = document.getElementById('msg-input-' + orderId);
    const msg = input.value.trim();
    if (!msg) return;

    fetch('/order/' + orderId + '/messages', {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
        body: JSON.stringify({message: msg, sender_name: customerName, sender_email: customerEmail})
    })
    .then(r => r.json())
    .then(() => { input.value = ''; loadMessages(orderId); })
    .catch(() => alert('Failed to send message. Please try again.'));
}

@foreach($orders as $order)
loadMessages({{ $order->id }});
@endforeach
</script>
@endif
@endisset
@endsection