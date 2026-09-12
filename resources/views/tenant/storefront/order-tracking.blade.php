@use(App\Presenters\OrderItemPresenter)
@php
    /** @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\Orders\Order>|null $orders */
    /** @var \Illuminate\Support\Collection<int, \App\Presenters\OrderTrackingPresenter>|null $trackedOrders */
    /** @var string|null $email */
    /** @var array<string, string> $trackableStatuses */
    /** @var array<string, string> $content */
@endphp

<x-layouts.storefront>
    <link rel="stylesheet" href="{{ asset('css/order-tracking.css') }}" />

    <div @class(['biscotto-order-followup biscotto-order-tracking' => $storefrontTheme === 'biscotto'])>
        {{-- Photo-Forward Hero --}}
        <x-storefront.hero-section
            :image="$settings->heroImageUrl()"
            image-alt="Track Your Order"
            image-class="hero-img"
        >
            <div class="relative z-10 flex min-h-[55vh] flex-col items-center justify-end px-4 pb-20 text-center">
                <x-storefront.eyebrow class="hero-fade-1 mb-6">
                    {{ $content['hero_eyebrow'] ?? 'Order Status' }}</x-storefront.eyebrow>
                <h1 class="hero-fade-1 font-display text-warm-100 mb-6 text-3xl leading-none font-bold sm:text-5xl md:text-7xl lg:text-8xl">
                    {{ $content['hero_title'] ?? 'Track Your Order' }}
                </h1>
                <p class="hero-fade-2 text-warm-100 mx-auto max-w-lg text-lg">
                    {{ $content['hero_subtitle'] ?? 'Enter your email to see how your order is coming along.' }}
                </p>
            </div>
        </x-storefront.hero-section>

        {{-- Email Lookup Form --}}
        <section class="bg-warm-100 relative py-16 md:py-20">
            <div class="mx-auto max-w-xl px-4">
                <form method="POST" action="{{ route('order.track.lookup') }}" class="hero-fade-3">
                    @csrf
                    <label
                        for="email"
                        class="text-warm-500 mb-3 block text-center text-xs font-medium tracking-[0.2em] uppercase"
                    >{{ $content['email_label'] ?? 'Email Address' }}</label>
                    <div class="flex gap-3">
                        <input
                            type="email"
                            name="email"
                            id="email"
                            class="track-input flex-1"
                            placeholder="you@example.com"
                            value="{{ old('email', $email ?? '') }}"
                            required
                        />
                        <x-storefront.button type="submit" size="md" fontDisplay class="flex-shrink-0">
                            {{ $content['lookup_button'] ?? 'Look Up' }}
                        </x-storefront.button>
                    </div>
                    @error('email')
                        <p class="mt-3 text-center text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </form>
            </div>
        </section>

        @isset($orders)
            @if ($orders->isEmpty())
                {{-- Empty state --}}
                <x-storefront.dark-section padding="py-24">
                    <div class="mx-auto max-w-md px-4 text-center">
                        <div class="bg-warm-500/10 border-warm-500/20 mx-auto mb-8 flex h-20 w-20 items-center justify-center rounded-full border">
                            <x-heroicon-o-magnifying-glass class="text-warm-500 h-8 w-8" />
                        </div>
                        <p class="font-display text-warm-100 mb-4 text-3xl font-bold md:text-4xl">
                            {{ $content['empty_heading'] ?? 'No orders found' }}
                        </p>
                        <p class="text-warm-400 mb-2 text-lg">
                            {{ $content['empty_description_prefix'] ?? 'We couldn\'t find any orders for' }}
                            <strong class="text-warm-300">{{ $email }}</strong>.
                        </p>
                        <p class="text-warm-300 text-sm">
                            {{ $content['empty_hint'] ?? 'Make sure you\'re using the same email you ordered with.' }}
                        </p>
                    </div>
                </x-storefront.dark-section>
            @else
                {{-- Orders List --}}
                <x-storefront.dark-section padding="py-20 md:py-24" radial-position="30% 50%">
                    <div class="mx-auto max-w-4xl px-4">
                        <x-storefront.section-divider tone="dark" class="mb-12">
                            {{ $orders->count() }} {{ Str::plural('order', $orders->count()) }} for {{ $email }}
                        </x-storefront.section-divider>

                        <div class="space-y-8">
                            @foreach ($trackedOrders as $tracked)
                                <div class="order-card bg-warm-800 border-warm-700/20 overflow-hidden rounded-2xl border">
                                    {{-- Order header --}}
                                    <div class="border-warm-700/15 flex flex-wrap items-start justify-between gap-4 border-b px-6 py-6 md:px-8">
                                        <div>
                                            <h3 class="font-display text-warm-100 text-xl font-bold">
                                                Order {{ $tracked->order->order_number }}
                                            </h3>
                                            <p class="text-warm-500 mt-1 text-sm">Placed {{ $tracked->placedAt() }}</p>
                                        </div>
                                        <div class="text-right">
                                            <p class="font-display text-warm-400 text-2xl font-bold">
                                                @money($tracked->order->total)
                                            </p>
                                            @if ($tracked->isCancelled)
                                                <span class="mt-1 inline-block rounded-full border border-red-500/30 bg-red-500/15 px-3 py-1 text-xs font-semibold tracking-wider text-red-400 uppercase">Cancelled</span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="space-y-8 px-6 py-8 md:px-8">
                                        {{-- Progress stepper --}}
                                        @unless ($tracked->isCancelled)
                                            <div>
                                                {{-- Desktop stepper --}}
                                                <div class="hidden sm:block">
                                                    <div class="relative flex items-center justify-between">
                                                        <div class="bg-warm-700/15 absolute top-4 right-0 left-0 h-1 rounded-full"></div>
                                                        @if ($tracked->currentStepIndex > 0)
                                                            <div
                                                                class="bg-warm-500 absolute top-4 left-0 h-1 rounded-full transition-all duration-700"
                                                                style="width: {{ $tracked->progressPercentage() }}%;"
                                                            ></div>
                                                        @endif

                                                        @foreach ($trackableStatuses as $i => $step)
                                                            <div
                                                                class="relative z-10 flex flex-col items-center"
                                                                style="width: {{ 100 / count($trackableStatuses) }}%;"
                                                            >
                                                                <div @class([
                                                                    'track-stepper-dot w-9 h-9 rounded-full flex items-center justify-center text-sm font-semibold',
                                                                    'bg-warm-500 text-warm-900' => $tracked->isStepCompleted($i),
                                                                    'bg-warm-700/15 text-warm-300' => ! $tracked->isStepCompleted($i),
                                                                    'ring-4 ring-warm-500/20' => $tracked->isCurrentStep($i),
                                                                ])>
                                                                    @if ($tracked->isStepCompleted($i) && ! $tracked->isCurrentStep($i))
                                                                        <x-heroicon-o-check
                                                                            class="h-4 w-4"
                                                                            stroke-width="3"
                                                                        />
                                                                    @else
                                                                        {{ $i + 1 }}
                                                                    @endif
                                                                </div>
                                                                <span @class([
                                                                    'mt-2 text-xs font-medium text-center',
                                                                    'text-warm-300' => $tracked->isStepCompleted($i),
                                                                    'text-warm-300' => ! $tracked->isStepCompleted($i),
                                                                ])>
                                                                    {{ $step->getLabel() }}
                                                                </span>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>

                                                {{-- Mobile stepper --}}
                                                <div class="space-y-3 sm:hidden">
                                                    @foreach ($trackableStatuses as $i => $step)
                                                        <div class="flex items-center gap-3">
                                                            <div @class([
                                                                'w-7 h-7 rounded-full flex items-center justify-center text-xs font-semibold flex-shrink-0',
                                                                'bg-warm-500 text-warm-900' => $tracked->isStepCompleted($i),
                                                                'bg-warm-700/15 text-warm-300' => ! $tracked->isStepCompleted($i),
                                                            ])>
                                                                @if ($tracked->isStepCompleted($i) && ! $tracked->isCurrentStep($i))
                                                                    <x-heroicon-o-check
                                                                        class="h-3.5 w-3.5"
                                                                        stroke-width="3"
                                                                    />
                                                                @else
                                                                    {{ $i + 1 }}
                                                                @endif
                                                            </div>
                                                            <span @class([
                                                                'text-sm font-medium',
                                                                'text-warm-200' => $tracked->isStepCompleted($i),
                                                                'text-warm-300' => ! $tracked->isStepCompleted($i),
                                                            ])>
                                                                {{ $step->getLabel() }}
                                                            </span>
                                                        </div>
                                                        @if ($i < count($trackableStatuses) - 1)
                                                            <div
                                                                @class([
                                                                    'ml-3 w-0.5 h-3',
                                                                    'bg-warm-500' => $tracked->isStepCompleted($i) && ! $tracked->isCurrentStep($i),
                                                                    'bg-warm-700/15' => ! ($tracked->isStepCompleted($i) && ! $tracked->isCurrentStep($i)),
                                                                ])
                                                            ></div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endunless

                                        {{-- Items Ordered --}}
                                        <div class="border-warm-700/15 border-t pt-6">
                                            <div class="mb-4 flex items-center gap-3">
                                                <span class="bg-warm-500 block h-px w-6 opacity-50"></span>
                                                <span class="text-warm-500 text-xs font-semibold tracking-[0.2em] uppercase">{{ $content['items_label'] ?? 'Items Ordered' }}</span>
                                            </div>
                                            <div class="space-y-3">
                                                @foreach ($tracked->order->orderItems as $item)
                                                    <div class="bg-warm-700/5 flex items-center justify-between rounded-xl px-4 py-2">
                                                        <span class="text-warm-200 text-sm">
                                                            {{ $item->product->name ?? 'Product' }}
                                                            <span class="text-warm-500 font-medium">× {{ $item->quantity }}</span>
                                                        </span>
                                                        <span class="text-warm-300 text-sm font-semibold">
                                                            @money(OrderItemPresenter::for($item)->totalPrice())
                                                        </span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>

                                        {{-- Messages --}}
                                        <div class="border-warm-700/15 border-t pt-6">
                                            <div class="mb-4 flex items-center gap-3">
                                                <span class="bg-warm-500 block h-px w-6 opacity-50"></span>
                                                <span class="text-warm-500 text-xs font-semibold tracking-[0.2em] uppercase">{{ $content['messages_label'] ?? 'Messages' }}</span>
                                            </div>
                                            <div
                                                id="messages-{{ $tracked->order->order_number }}"
                                                class="bg-warm-700/5 mb-4 max-h-64 space-y-3 overflow-y-auto rounded-xl p-4"
                                            >
                                                <p class="text-warm-300 text-sm italic">Loading messages...</p>
                                            </div>
                                            <form
                                                onsubmit="sendOrderMessage(event, '{{ $tracked->order->order_number }}')"
                                                class="flex gap-2"
                                            >
                                                <input
                                                    type="text"
                                                    id="msg-input-{{ $tracked->order->order_number }}"
                                                    placeholder="Type a message..."
                                                    class="track-msg-input flex-1"
                                                    required
                                                />
                                                <x-storefront.button type="submit" size="sm">Send</x-storefront.button>
                                            </form>
                                        </div>

                                        {{-- Reorder --}}
                                        <div class="border-warm-700/15 flex justify-center border-t pt-6">
                                            <a
                                                href="{{ route('order.create') }}?reorder={{ $tracked->order->order_number }}"
                                                class="bg-warm-500/10 text-warm-400 border-warm-500/25 hover:bg-warm-500 hover:text-warm-900 hover:border-warm-500 inline-flex items-center gap-2 rounded-full border px-6 py-3 text-sm font-semibold transition-all duration-300 hover:scale-105"
                                            >
                                                <x-heroicon-o-arrow-path class="h-4 w-4" stroke-width="2" />
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
                <script @cspnonce>
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
                        messages.forEach((msg) => {
                            const isBaker = msg.sender_type === 'baker';
                            const cls = isBaker ? 'track-message-baker' : 'track-message-customer';
                            const align = isBaker ? 'items-start' : 'items-end';
                            const time = new Date(msg.created_at).toLocaleString([], {
                                month: 'short',
                                day: 'numeric',
                                hour: '2-digit',
                                minute: '2-digit',
                            });

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
                            .then((r) => r.json())
                            .then((data) => renderMessages(orderId, data.messages))
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
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: JSON.stringify({ message: msg, sender_name: customerName, sender_email: customerEmail }),
                        })
                            .then((r) => r.json())
                            .then(() => {
                                input.value = '';
                                loadMessages(orderId);
                            })
                            .catch(() => alert('Failed to send message. Please try again.'));
                    }

                    @foreach ($orders as $order)
loadMessages('{{ $order->order_number }}');
@endforeach
                </script>
            @endif
        @endisset
    </div>
</x-layouts.storefront>
