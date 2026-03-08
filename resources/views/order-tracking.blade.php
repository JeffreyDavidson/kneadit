@extends('layouts.storefront')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-12">
    <div class="text-center mb-10">
        <h1 class="font-display text-4xl font-bold mb-3" style="color: var(--warm-900);">Track Your Order</h1>
        <p style="color: var(--warm-700);">Enter your email address to view your order status.</p>
    </div>

    {{-- Email lookup form --}}
    <div class="card p-6 mb-10 max-w-lg mx-auto">
        <form method="POST" action="{{ route('order.track.lookup') }}">
            @csrf
            <label for="email" class="block font-medium mb-2" style="color: var(--warm-900);">Email Address</label>
            <input
                type="email"
                name="email"
                id="email"
                class="input-field mb-4"
                placeholder="you@example.com"
                value="{{ old('email', $email ?? '') }}"
                required
            >
            @error('email')
                <p class="text-red-600 text-sm mb-4">{{ $message }}</p>
            @enderror
            <button type="submit" class="btn-primary w-full text-center">Look Up Orders</button>
        </form>
    </div>

    @isset($orders)
        @if($orders->isEmpty())
            {{-- Empty state --}}
            <div class="card p-10 text-center">
                <div class="w-16 h-16 rounded-full mx-auto mb-4 flex items-center justify-center" style="background: var(--warm-200);">
                    <svg class="w-8 h-8" style="color: var(--warm-600);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h2 class="font-display text-2xl font-semibold mb-2" style="color: var(--warm-900);">No Orders Found</h2>
                <p style="color: var(--warm-700);">We couldn't find any orders for <strong>{{ $email }}</strong>.</p>
                <p class="mt-2" style="color: var(--warm-600);">Make sure you're using the same email you placed your order with.</p>
            </div>
        @else
            <h2 class="font-display text-2xl font-semibold mb-6" style="color: var(--warm-900);">
                Orders for {{ $email }}
            </h2>

            @php
                $allStatuses = ['pending', 'confirmed', 'baking', 'ready', 'delivered'];
                $statusLabels = [
                    'pending' => 'Pending',
                    'confirmed' => 'Confirmed',
                    'baking' => 'Baking',
                    'ready' => 'Ready',
                    'delivered' => 'Delivered',
                ];
            @endphp

            <div class="space-y-8">
                @foreach($orders as $order)
                    @php
                        $isCancelled = $order->status === 'cancelled';
                        $currentIndex = array_search($order->status, $allStatuses);
                        if ($currentIndex === false) $currentIndex = -1;
                    @endphp
                    <div class="card p-6">
                        {{-- Header --}}
                        <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
                            <div>
                                <h3 class="font-display text-xl font-semibold" style="color: var(--warm-900);">
                                    Order {{ $order->order_number }}
                                </h3>
                                <p class="text-sm mt-1" style="color: var(--warm-600);">
                                    Placed {{ $order->created_at->format('M j, Y \a\t g:i A') }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-lg" style="color: var(--warm-900);">${{ number_format($order->total, 2) }}</p>
                                @if($isCancelled)
                                    <span class="inline-block mt-1 px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">Cancelled</span>
                                @endif
                            </div>
                        </div>

                        {{-- Progress stepper --}}
                        @unless($isCancelled)
                        <div class="mb-6">
                            {{-- Desktop stepper --}}
                            <div class="hidden sm:block">
                                <div class="flex items-center justify-between relative">
                                    {{-- Background bar --}}
                                    <div class="absolute top-4 left-0 right-0 h-1 rounded" style="background: var(--warm-200);"></div>
                                    {{-- Filled bar --}}
                                    @if($currentIndex > 0)
                                    <div class="absolute top-4 left-0 h-1 rounded transition-all duration-500" style="background: var(--warm-500); width: {{ ($currentIndex / (count($allStatuses) - 1)) * 100 }}%;"></div>
                                    @endif

                                    @foreach($allStatuses as $i => $step)
                                        @php
                                            $isCompleted = $i <= $currentIndex;
                                        @endphp
                                        <div class="relative z-10 flex flex-col items-center" style="width: {{ 100 / count($allStatuses) }}%;">
                                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-semibold transition-all duration-300 {{ $isCompleted ? 'text-white' : '' }}"
                                                 style="background: {{ $isCompleted ? 'var(--warm-500)' : 'var(--warm-200)' }}; color: {{ $isCompleted ? 'white' : 'var(--warm-600)' }};">
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
                                             style="background: {{ $isCompleted ? 'var(--warm-500)' : 'var(--warm-200)' }}; color: {{ $isCompleted ? 'white' : 'var(--warm-600)' }};">
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

                        {{-- Reorder Button --}}
                        <div class="border-t pt-4 mb-4" style="border-color: var(--warm-200);">
                            <a href="{{ route('order.create') }}?reorder={{ $order->id }}"
                               class="btn-primary inline-flex items-center gap-2 text-sm px-4 py-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                </svg>
                                Order Again
                            </a>
                        </div>

                        {{-- Items --}}
                        <div class="border-t pt-4" style="border-color: var(--warm-200);">
                            <h4 class="font-medium mb-3 text-sm" style="color: var(--warm-700);">Items Ordered</h4>
                            <div class="space-y-2">
                                @foreach($order->orderItems as $item)
                                    <div class="flex justify-between text-sm">
                                        <span style="color: var(--warm-900);">
                                            {{ $item->product->name ?? 'Product' }}
                                            <span style="color: var(--warm-600);">&times; {{ $item->quantity }}</span>
                                        </span>
                                        <span style="color: var(--warm-700);">${{ number_format($item->total_price, 2) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Messages --}}
                        <div class="border-t pt-4 mt-4" style="border-color: var(--warm-200);">
                            <h4 class="font-medium mb-3 text-sm flex items-center gap-2" style="color: var(--warm-700);">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                Messages
                            </h4>
                            <div id="messages-{{ $order->id }}" class="space-y-3 mb-4 max-h-64 overflow-y-auto">
                                <p class="text-sm italic" style="color: var(--warm-500);">Loading messages...</p>
                            </div>
                            <form onsubmit="sendOrderMessage(event, {{ $order->id }})" class="flex gap-2">
                                <input type="text" id="msg-input-{{ $order->id }}" placeholder="Type a message..." class="input-field flex-1 text-sm" required>
                                <button type="submit" class="btn-primary text-sm px-4 py-2">Send</button>
                            </form>
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
        container.innerHTML = '<p class="text-sm italic" style="color: var(--warm-500);">No messages yet. Send one below!</p>';
        return;
    }
    container.innerHTML = messages.map(msg => {
        const isBaker = msg.sender_type === 'baker';
        const align = isBaker ? 'items-start' : 'items-end';
        const bg = isBaker ? 'background: #fef3c7' : 'background: #78350f; color: white';
        const time = new Date(msg.created_at).toLocaleString([], {month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'});
        return `<div class="flex flex-col ${align}">
            <div class="rounded-lg px-3 py-2 max-w-xs text-sm" style="${bg}">
                <p class="font-medium text-xs mb-1" style="${isBaker ? 'color: var(--warm-700)' : 'opacity:0.8'}">${msg.sender_name}</p>
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
            document.getElementById('messages-' + orderId).innerHTML = '<p class="text-sm italic" style="color: var(--warm-500);">Could not load messages.</p>';
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
