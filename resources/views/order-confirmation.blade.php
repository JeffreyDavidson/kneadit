@extends('layouts.storefront')

@php
    $heroImage = \App\Models\Setting::get('hero_image');
    $heroImageUrl = $heroImage ? Storage::url($heroImage) : 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=1920&q=80';
@endphp

@section('content')
@php
    $content = \App\Models\Setting::pageContentAll('order_confirmation');
    $journeySteps = $content['journey_steps'] ?? [
        ['title' => 'Confirmation', 'description' => 'You\'ll receive an email confirmation with your order details shortly.'],
        ['title' => 'Preparation', 'description' => 'Our bakers will craft your items fresh on your scheduled date.'],
        ['title' => 'Delivery', 'description_delivery' => 'We\'ll deliver your fresh items right to your door.', 'description_pickup' => 'Your items will be warm and ready for you to pick up.'],
    ];
@endphp
{{-- Photo-Forward Hero with Success --}}
<section class="relative overflow-hidden" style="min-height: 40vh;">
    <div class="absolute inset-0">
        <img src="{{ $heroImageUrl }}" alt="Order confirmed" class="w-full h-full object-cover confirmation-hero-img">
    </div>
    <div class="absolute inset-0" style="background: linear-gradient(to bottom, rgba(28,20,16,0.4) 0%, rgba(28,20,16,0.65) 50%, rgba(28,20,16,0.95) 100%);"></div>
    <div class="absolute inset-0 opacity-[0.03]" style="background-image: url('data:image/svg+xml,%3Csvg viewBox=%220 0 256 256%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cfilter id=%22noise%22%3E%3CfeTurbulence type=%22fractalNoise%22 baseFrequency=%220.9%22 numOctaves=%224%22 stitchTiles=%22stitch%22/%3E%3C/filter%3E%3Crect width=%22100%25%22 height=%22100%25%22 filter=%22url(%23noise)%22/%3E%3C/svg%3E');"></div>

    <div class="relative z-10 text-center px-4 py-24 md:py-32" style="padding-top: 6rem;">
        {{-- Animated success checkmark --}}
        <div class="inline-flex items-center justify-center w-24 h-24 rounded-full mb-8 confirmation-fade-up" style="background: rgba(212,146,12,0.15); border: 2px solid var(--warm-500); animation-delay: 0.3s;">
            <svg class="w-12 h-12" style="color: var(--warm-500);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>

        <div class="flex items-center justify-center gap-3 mb-4 confirmation-fade-up" style="animation-delay: 0.5s;">
            <span class="block w-8 h-px" style="background: var(--warm-500);"></span>
            <span class="uppercase tracking-[0.25em] text-xs font-semibold" style="color: var(--warm-500);">{{ $content['hero_eyebrow'] ?? 'Order Placed' }}</span>
            <span class="block w-8 h-px" style="background: var(--warm-500);"></span>
        </div>

        <h1 class="font-display text-4xl md:text-6xl font-bold mb-4 confirmation-fade-up" style="color: var(--warm-100); animation-delay: 0.7s;">
            {{ $content['hero_title'] ?? 'Thank You!' }}
        </h1>
        <p class="text-lg mb-3 max-w-lg mx-auto confirmation-fade-up" style="color: var(--warm-400); animation-delay: 0.9s;">
            {{ $content['hero_description'] ?? 'Your order has been received and we\'ll start preparing your items right away.' }}
        </p>
        <div class="inline-block px-6 py-3 rounded-full confirmation-fade-up" style="background: rgba(212,146,12,0.1); border: 1px solid rgba(212,146,12,0.25); animation-delay: 1.1s;">
            <span class="text-sm font-medium" style="color: var(--warm-400);">Order Number:</span>
            <span class="font-mono font-bold ml-2" style="color: var(--warm-300);">{{ $order->order_number }}</span>
        </div>
    </div>
</section>

{{-- Order Details --}}
<section style="background: var(--warm-900);">
    <div class="max-w-5xl mx-auto px-4 pb-24">
        <div class="grid md:grid-cols-2 gap-8">
            {{-- Items & Totals --}}
            <div class="rounded-2xl p-6 md:p-8" style="background: var(--warm-800); border: 1px solid rgba(139,104,68,0.2);">
                <div class="flex items-center gap-3 mb-6">
                    <span class="block w-8 h-px" style="background: var(--warm-500);"></span>
                    <h2 class="font-display text-xl font-semibold" style="color: var(--warm-100);">{{ $content['details_heading'] ?? 'Order Details' }}</h2>
                </div>

                <div class="space-y-3 mb-6">
                    @foreach($order->orderItems as $item)
                    <div class="flex justify-between items-center py-2" style="border-bottom: 1px solid rgba(139,104,68,0.12);">
                        <div>
                            <span class="font-medium" style="color: var(--warm-200);">{{ $item->product->name ?? 'Product' }}</span>
                            <span class="text-sm ml-2" style="color: var(--warm-500);">× {{ $item->quantity }}</span>
                        </div>
                        <span class="font-semibold" style="color: var(--warm-300);">${{ number_format($item->total_price, 2) }}</span>
                    </div>
                    @endforeach
                </div>

                <div class="space-y-2 pt-4 text-sm" style="border-top: 1px solid rgba(139,104,68,0.2);">
                    <div class="flex justify-between">
                        <span style="color: var(--warm-500);">Subtotal</span>
                        <span style="color: var(--warm-300);">${{ number_format($order->subtotal, 2) }}</span>
                    </div>
                    @if($order->delivery_fee > 0)
                    <div class="flex justify-between">
                        <span style="color: var(--warm-500);">Delivery Fee</span>
                        <span style="color: var(--warm-300);">${{ number_format($order->delivery_fee, 2) }}</span>
                    </div>
                    @endif
                    @if($order->discount_amount > 0)
                    <div class="flex justify-between" style="color: #4ade80;">
                        <span>Discount @if($order->coupon)({{ $order->coupon->code }})@endif</span>
                        <span>-${{ number_format($order->discount_amount, 2) }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between pt-3" style="border-top: 1px solid rgba(139,104,68,0.2);">
                        <span class="font-display text-lg font-bold" style="color: var(--warm-100);">Total</span>
                        <span class="font-display text-2xl font-bold" style="color: var(--warm-400);">${{ number_format($order->total, 2) }}</span>
                    </div>
                </div>
            </div>

            {{-- Customer & Delivery Info --}}
            <div class="rounded-2xl p-6 md:p-8" style="background: var(--warm-800); border: 1px solid rgba(139,104,68,0.2);">
                <div class="flex items-center gap-3 mb-6">
                    <span class="block w-8 h-px" style="background: var(--warm-500);"></span>
                    <h2 class="font-display text-xl font-semibold" style="color: var(--warm-100);">
                        {{ $order->delivery_type->value === 'delivery' ? 'Delivery' : 'Pickup' }} Details
                    </h2>
                </div>

                <div class="space-y-5">
                    <div>
                        <span class="block text-xs uppercase tracking-wider font-medium mb-1" style="color: var(--warm-500);">Customer</span>
                        <p style="color: var(--warm-200);">{{ $order->customer->name }}</p>
                        <p class="text-sm" style="color: var(--warm-400);">{{ $order->customer->email }}</p>
                        @if($order->customer->phone)
                        <p class="text-sm" style="color: var(--warm-400);">{{ $order->customer->phone }}</p>
                        @endif
                    </div>

                    <div>
                        <span class="block text-xs uppercase tracking-wider font-medium mb-1" style="color: var(--warm-500);">Date & Time</span>
                        <p style="color: var(--warm-200);">{{ \Carbon\Carbon::parse($order->delivery_date)->format('l, F j, Y') }}</p>
                        @if($order->delivery_time)
                        <p class="text-sm" style="color: var(--warm-400);">{{ $order->delivery_time }}</p>
                        @endif
                    </div>

                    @if($order->delivery_type->value === 'delivery' && $order->delivery_address)
                    <div>
                        <span class="block text-xs uppercase tracking-wider font-medium mb-1" style="color: var(--warm-500);">Delivery Address</span>
                        <p style="color: var(--warm-200);">{{ $order->delivery_address }}</p>
                    </div>
                    @endif

                    @if($order->notes)
                    <div>
                        <span class="block text-xs uppercase tracking-wider font-medium mb-1" style="color: var(--warm-500);">Special Instructions</span>
                        <p class="text-sm" style="color: var(--warm-300);">{{ $order->notes }}</p>
                    </div>
                    @endif

                    <div>
                        <span class="block text-xs uppercase tracking-wider font-medium mb-1" style="color: var(--warm-500);">Status</span>
                        <span class="inline-block px-4 py-1.5 rounded-full text-sm font-semibold" style="background: rgba(212,146,12,0.15); color: var(--warm-400); border: 1px solid rgba(212,146,12,0.3);">
                            {{ ucfirst($order->status) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- What Happens Next --}}
        <div class="rounded-2xl p-8 md:p-12 mt-8" style="background: var(--warm-800); border: 1px solid rgba(139,104,68,0.2);">
            <div class="text-center mb-10">
                <span class="uppercase tracking-[0.25em] text-xs font-semibold" style="color: var(--warm-500);">{{ $content['journey_eyebrow'] ?? 'What Happens Next' }}</span>
                <h2 class="font-display text-3xl font-bold mt-2" style="color: var(--warm-100);">{{ $content['journey_heading'] ?? 'Your Order Journey' }}</h2>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                @foreach($journeySteps as $stepIndex => $step)
                <div class="text-center">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4" style="background: rgba(212,146,12,0.15); border: 1.5px solid rgba(212,146,12,0.3);">
                        <span class="font-display text-xl font-bold" style="color: var(--warm-400);">{{ $stepIndex + 1 }}</span>
                    </div>
                    <h3 class="font-display text-lg font-semibold mb-2" style="color: var(--warm-200);">
                        @if(isset($step['description_delivery']) || isset($step['description_pickup']))
                            {{ $order->delivery_type->value === 'delivery' ? 'Delivery' : 'Pickup' }}
                        @else
                            {{ $step['title'] }}
                        @endif
                    </h3>
                    <p class="text-sm" style="color: var(--warm-500);">
                        @if(isset($step['description_delivery']) || isset($step['description_pickup']))
                            @if($order->delivery_type->value === 'delivery')
                                {{ $step['description_delivery'] ?? 'We\'ll deliver your fresh items right to your door.' }}
                            @else
                                {{ $step['description_pickup'] ?? 'Your items will be warm and ready for you to pick up.' }}
                            @endif
                        @else
                            {{ $step['description'] }}
                        @endif
                    </p>
                </div>
                @endforeach
            </div>

            <div class="text-center mt-10 pt-8" style="border-top: 1px solid rgba(139,104,68,0.2);">
                <p class="text-sm mb-6" style="color: var(--warm-500);">
                    Questions? Reference order <strong style="color: var(--warm-400);">{{ $order->order_number }}</strong> when you
                    <a href="{{ route('contact.show') }}" class="underline" style="color: var(--warm-400);">contact us</a>.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                    <a href="{{ route('order.track') }}" class="inline-block px-8 py-3 rounded-full font-semibold transition-all duration-300 hover:scale-105" style="background: var(--warm-500); color: var(--warm-900);">
                        Track Your Order
                    </a>
                    <a href="{{ url('/') }}" class="inline-flex items-center gap-2 px-6 py-3 font-semibold transition-all" style="color: var(--warm-400);">
                        Back to {{ \App\Models\Setting::get('store_name', 'Home') }}
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    </a>
                    <button onclick="window.print()" class="inline-flex items-center gap-2 px-6 py-3 font-semibold transition-all" style="color: var(--warm-500);">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        Print
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('styles')
<style>
@keyframes confirmationKenBurns {
    0% { transform: scale(1); }
    100% { transform: scale(1.06); }
}
@keyframes confirmationFadeUp {
    from { opacity: 0; transform: translateY(30px); }
    to { opacity: 1; transform: translateY(0); }
}
.confirmation-hero-img {
    animation: confirmationKenBurns 25s ease-out forwards;
}
.confirmation-fade-up {
    opacity: 0;
    animation: confirmationFadeUp 0.8s ease-out forwards;
}
@media print {
    nav, footer, button, a[href] { display: none !important; }
    body { background: white !important; }
    section { background: white !important; }
    * { color: #1a1a1a !important; border-color: #ddd !important; }
}
</style>
@endsection