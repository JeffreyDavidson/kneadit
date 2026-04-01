@use('App\Enums\Orders\OrderStatus')

@php
/** @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\Orders\Order>|null $orders */
/** @var string|null $email */
/** @var array<string, string> $trackableStatuses */
/** @var array<string, string> $content */
@endphp

<x-layouts.storefront>

<link rel="stylesheet" href="{{ asset('css/order-tracking.css') }}">

{{-- Photo-Forward Hero --}}
<x-storefront.hero-section :image="$settings->heroImageUrl()" image-alt="Track Your Order" image-class="track-hero-img">
    <div class="relative z-10 flex flex-col items-center justify-end text-center px-4 pb-20 min-h-[55vh]">
        <x-storefront.eyebrow class="track-fade-1 mb-6">{{ $content['hero_eyebrow'] ?? 'Order Status' }}</x-storefront.eyebrow>
        <h1 class="track-fade-1 font-display text-5xl md:text-7xl lg:text-8xl font-bold leading-none mb-6 text-warm-100">
            {{ $content['hero_title'] ?? 'Track Your Order' }}
        </h1>
        <p class="track-fade-2 text-lg max-w-lg mx-auto text-warm-400">
            {{ $content['hero_subtitle'] ?? 'Enter your email to see how your order is coming along.' }}
        </p>
    </div>
</x-storefront.hero-section>

{{-- Email Lookup Form --}}
<section class="relative py-16 md:py-20 bg-warm-100">
    <div class="max-w-xl mx-auto px-4">
        <form method="POST" action="{{ route('order.track.lookup') }}" class="track-fade-3">
            @csrf
            <label for="email" class="block text-xs font-medium uppercase tracking-[0.2em] mb-3 text-center text-warm-500">{{ $content['email_label'] ?? 'Email Address' }}</label>
            <div class="flex gap-3">
                <input type="email" name="email" id="email" class="track-input flex-1"
                       placeholder="you@example.com" value="{{ old('email', $email ?? '') }}" required>
                <button type="submit" class="px-8 py-3 rounded-full font-semibold transition-all duration-300 hover:scale-105 hover:shadow-xl flex-shrink-0" style="background: var(--warm-500); color: var(--warm-900); font-family: var(--font-display);">
                    {{ $content['lookup_button'] ?? 'Look Up' }}
                </button>
            </div>
            @error('email')
                <p class="text-red-500 text-sm mt-3 text-center">{{ $message }}</p>
            @enderror
        </form>
    </div>
</section>

@isset($orders)
    @if ($orders->isEmpty())
    {{-- Empty state --}}
    <x-storefront.dark-section padding="py-24">
        <div class="text-center max-w-md mx-auto px-4">
            <div class="w-20 h-20 rounded-full mx-auto mb-8 flex items-center justify-center" style="background: rgba(212,146,12,0.1); border: 1px solid rgba(212,146,12,0.2);">
                <svg class="w-8 h-8 text-warm-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <p class="font-display text-3xl md:text-4xl font-bold mb-4 text-warm-100">{{ $content['empty_heading'] ?? 'No orders found' }}</p>
            <p class="text-lg mb-2 text-warm-400">
                {{ $content['empty_description_prefix'] ?? 'We couldn\'t find any orders for' }} <strong class="text-warm-300">{{ $email }}</strong>.
            </p>
            <p class="text-sm text-warm-600">{{ $content['empty_hint'] ?? 'Make sure you\'re using the same email you ordered with.' }}</p>
        </div>
    </x-storefront.dark-section>
    @else


    {{-- Orders List --}}
    <x-storefront.dark-section padding="py-20 md:py-24" radial-position="30% 50%">
        <div class="max-w-4xl mx-auto px-4">
            <div class="flex items-center gap-6 mb-12">
                <div class="flex-1 h-px" style="background: rgba(139,104,68,0.25);"></div>
                <span class="uppercase tracking-[0.25em] text-xs font-semibold whitespace-nowrap text-warm-500">
                    {{ $orders->count() }} {{ Str::plural('order', $orders->count()) }} for {{ $email }}
                </span>
                <div class="flex-1 h-px" style="background: rgba(139,104,68,0.25);"></div>
            </div>

            <div class="space-y-8">
                @foreach ($orders as $order)
                    @php
                        $isCancelled = $order->status === OrderStatus::Cancelled;
                        $currentIndex = array_search($order->status, $trackableStatuses);
                        if ($currentIndex === false) $currentIndex = -1;
                    @endphp
                    <div class="order-card rounded-2xl overflow-hidden bg-warm-800 border border-warm-700/20">
                        {{-- Order header --}}
                        <div class="px-6 md:px-8 py-6 flex flex-wrap items-start justify-between gap-4" style="border-bottom: 1px solid rgba(139,104,68,0.15);">
                            <div>
                                <h3 class="font-display text-xl font-bold text-warm-100">
                                    Order {{ $order->order_number }}
                                </h3>
                                <p class="text-sm mt-1 text-warm-500">
                                    Placed {{ $order->created_at->format('M j, Y \a\t g:i A') }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="font-display text-2xl font-bold text-warm-400">${{ number_format($order->total, 2) }}</p>
                                @if ($isCancelled)
                                    <span class="inline-block mt-1 px-3 py-1 rounded-full text-xs font-semibold uppercase tracking-wider" style="background: rgba(239,68,68,0.15); color: #f87171; border: 1px solid rgba(239,68,68,0.3);">Cancelled</span>
                                @endif
                            </div>
                        </div>

                        <div class="px-6 md:px-8 py-8 space-y-8">
                            {{-- Progress stepper --}}
                            @unless ($isCancelled)
                            <div>
                                {{-- Desktop stepper --}}
                                <div class="hidden sm:block">
                                    <div class="flex items-center justify-between relative">
                                        <div class="absolute top-4 left-0 right-0 h-1 rounded-full" style="background: rgba(139,104,68,0.15);"></div>
                                        @if ($currentIndex > 0)
                                        <div class="absolute top-4 left-0 h-1 rounded-full transition-all duration-700" style="background: var(--warm-500); width: {{ ($currentIndex / (count($trackableStatuses) - 1)) * 100 }}%;"></div>
                                        @endif

                                        @foreach ($trackableStatuses as $i => $step)
                                            @php $isCompleted = $i <= $currentIndex; @endphp
                                            <div class="relative z-10 flex flex-col items-center" style="width: {{ 100 / count($trackableStatuses) }}%;">
                                                <div class="track-stepper-dot w-9 h-9 rounded-full flex items-center justify-center text-sm font-semibold"
                                                     style="background: {{ $isCompleted ? 'var(--warm-500)' : 'rgba(139,104,68,0.15)' }}; color: {{ $isCompleted ? 'var(--warm-900)' : 'var(--warm-600)' }}; {{ $isCompleted && $i === $currentIndex ? 'box-shadow: 0 0 0 4px rgba(212,146,12,0.2);' : '' }}">
                                                    @if ($isCompleted && $i < $currentIndex)
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                                    @else
                                                        {{ $i + 1 }}
                                                    @endif
                                                </div>
                                                <span class="mt-2 text-xs font-medium text-center" style="color: {{ $isCompleted ? 'var(--warm-300)' : 'var(--warm-600)' }};">
                                                    {{ $step->getLabel() }}
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                {{-- Mobile stepper --}}
                                <div class="sm:hidden space-y-3">
                                    @foreach ($trackableStatuses as $i => $step)
                                        @php $isCompleted = $i <= $currentIndex; @endphp
                                        <div class="flex items-center gap-3">
                                            <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-semibold flex-shrink-0"
                                                 style="background: {{ $isCompleted ? 'var(--warm-500)' : 'rgba(139,104,68,0.15)' }}; color: {{ $isCompleted ? 'var(--warm-900)' : 'var(--warm-600)' }};">
                                                @if ($isCompleted && $i < $currentIndex)
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                                @else
                                                    {{ $i + 1 }}
                                                @endif
                                            </div>
                                            <span class="text-sm font-medium" style="color: {{ $isCompleted ? 'var(--warm-200)' : 'var(--warm-600)' }};">
                                                {{ $step->getLabel() }}
                                            </span>
                                        </div>
                                        @if ($i < count($trackableStatuses) - 1)
                                            <div class="ml-3 w-0.5 h-3" style="background: {{ $i < $currentIndex ? 'var(--warm-500)' : 'rgba(139,104,68,0.15)' }};"></div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                            @endunless

                            {{-- Items Ordered --}}
                            <div class="pt-6 border-t border-warm-700/15">
                                <div class="flex items-center gap-3 mb-4">
                                    <span class="block w-6 h-px" style="background: var(--warm-500); opacity: 0.5;"></span>
                                    <span class="text-xs uppercase tracking-[0.2em] font-semibold text-warm-500">{{ $content['items_label'] ?? 'Items Ordered' }}</span>
                                </div>
                                <div class="space-y-3">
                                    @foreach ($order->orderItems as $item)
                                        <div class="flex justify-between items-center py-2 px-4 rounded-xl" style="background: rgba(139,104,68,0.06);">
                                            <span class="text-sm text-warm-200">
                                                {{ $item->product->name ?? 'Product' }}
                                                <span class="font-medium text-warm-500">× {{ $item->quantity }}</span>
                                            </span>
                                            <span class="text-sm font-semibold text-warm-300">${{ number_format($item->total_price, 2) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Messages --}}
                            <div class="pt-6 border-t border-warm-700/15">
                                <div class="flex items-center gap-3 mb-4">
                                    <span class="block w-6 h-px" style="background: var(--warm-500); opacity: 0.5;"></span>
                                    <span class="text-xs uppercase tracking-[0.2em] font-semibold text-warm-500">{{ $content['messages_label'] ?? 'Messages' }}</span>
                                </div>
                                <div id="messages-{{ $order->id }}" class="space-y-3 mb-4 max-h-64 overflow-y-auto rounded-xl p-4" style="background: rgba(139,104,68,0.06);">
                                    <p class="text-sm italic text-warm-600">Loading messages...</p>
                                </div>
                                <form onsubmit="sendOrderMessage(event, {{ $order->id }})" class="flex gap-2">
                                    <input type="text" id="msg-input-{{ $order->id }}" placeholder="Type a message..." class="track-msg-input flex-1" required>
                                    <button type="submit" class="px-6 py-2.5 rounded-full text-sm font-semibold transition-all duration-200 hover:scale-105 bg-warm-500 text-warm-900">Send</button>
                                </form>
                            </div>

                            {{-- Reorder --}}
                            <div class="pt-6 flex justify-center border-t border-warm-700/15">
                                <a href="{{ route('order.create') }}?reorder={{ $order->id }}"
                                   class="inline-flex items-center gap-2 text-sm font-semibold px-6 py-3 rounded-full transition-all duration-300 hover:scale-105"
                                   style="background: rgba(212,146,12,0.1); color: var(--warm-400); border: 1px solid rgba(212,146,12,0.25);"
                                   onmouseover="this.style.background='var(--warm-500)';this.style.color='var(--warm-900)'"
                                   onmouseout="this.style.background='rgba(212,146,12,0.1)';this.style.color='var(--warm-400)'">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                    {{ $content['reorder_button'] ?? 'Order Again' }}
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </x-storefront.dark-section>
    @endif
@endisset

{{-- CTA --}}
@empty($orders)
<x-storefront.cta-section
    :script-text="$content['cta_script'] ?? 'Ready to order?'"
    :heading="$content['cta_heading'] ?? 'Start your first order today.'"
    :button-text="$content['cta_button'] ?? 'Order Now'"
    :button-route="route('order.create')"
/>
@endempty

@isset($orders)
@if ($orders->isNotEmpty())
<script>
const customerEmail = @json($email);
const customerName = @json($orders->first()->customer->name ?? $email);

function renderMessages(orderId, messages) {
    const container = document.getElementById('messages-' + orderId);
    if (!messages.length) {
        container.textContent = '';
        const emptyP = document.createElement('p');
        emptyP.className = 'text-sm italic';
        emptyP.style.color = 'var(--warm-600)';
        emptyP.textContent = 'No messages yet. Say hello!';
        container.appendChild(emptyP);
        return;
    }
    container.textContent = '';
    messages.forEach(msg => {
        const isBaker = msg.sender_type === 'baker';
        const cls = isBaker ? 'track-message-baker' : 'track-message-customer';
        const align = isBaker ? 'items-start' : 'items-end';
        const time = new Date(msg.created_at).toLocaleString([], {month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'});

        const wrapper = document.createElement('div');
        wrapper.className = 'flex flex-col ' + align;

        const bubble = document.createElement('div');
        bubble.className = cls + ' px-4 py-3 max-w-xs text-sm';
        bubble.style.color = 'var(--warm-200)';

        const nameP = document.createElement('p');
        nameP.className = 'font-medium text-xs mb-1';
        nameP.style.color = 'var(--warm-500)';
        nameP.textContent = msg.sender_name;

        const msgP = document.createElement('p');
        msgP.className = 'whitespace-pre-wrap';
        msgP.textContent = msg.message;

        bubble.appendChild(nameP);
        bubble.appendChild(msgP);

        const timeSpan = document.createElement('span');
        timeSpan.className = 'text-xs mt-1';
        timeSpan.style.color = 'var(--warm-600)';
        timeSpan.textContent = time;

        wrapper.appendChild(bubble);
        wrapper.appendChild(timeSpan);
        container.appendChild(wrapper);
    });
    container.scrollTop = container.scrollHeight;
}

function loadMessages(orderId) {
    fetch('/order/' + orderId + '/messages')
        .then(r => r.json())
        .then(data => renderMessages(orderId, data.messages))
        .catch(() => {
            const c = document.getElementById('messages-' + orderId);
            c.textContent = '';
            const p = document.createElement('p');
            p.className = 'text-sm italic';
            p.style.color = 'var(--warm-600)';
            p.textContent = 'Could not load messages.';
            c.appendChild(p);
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

@foreach ($orders as $order)
loadMessages({{ $order->id }});
@endforeach
</script>
@endif
@endisset
</x-layouts.storefront>
