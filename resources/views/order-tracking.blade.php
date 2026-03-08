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
                    </div>
                @endforeach
            </div>
        @endif
    @endisset
</div>
@endsection
