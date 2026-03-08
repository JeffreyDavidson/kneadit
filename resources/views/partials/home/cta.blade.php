@php
    $heading = $config['heading'] ?? 'Treat Yourself Today';
    $subtext = $config['subtext'] ?? null;
    $buttonText = $config['button_text'] ?? 'Start Your Order';
    $buttonLink = $config['button_link'] ?? 'order';
    $leadTimeHours = \App\Models\Setting::get('order_lead_time_hours', '24');
    $storeName = \App\Models\Setting::get('store_name', 'Our Bakery');

    $linkMap = [
        'order' => route('order.create'),
        'menu' => route('storefront.menu'),
        'contact' => route('contact.show'),
    ];
    $href = $linkMap[$buttonLink] ?? route('order.create');
@endphp

<x-storefront.section bg="dark" padding="xl" maxWidth="3xl">
    <div class="relative text-center">
        {{-- Subtle radial glow --}}
        <div class="absolute inset-0 -m-20 pointer-events-none" style="background: radial-gradient(ellipse at center, rgba(212, 146, 12, 0.08), transparent 70%);"></div>

        <div class="relative z-10">
            <x-storefront.divider style="dot" width="sm" :dark="true" />

            <p class="font-script text-2xl md:text-3xl mb-4" style="color: var(--warm-500);">Ready to order?</p>

            <h2 class="font-display text-3xl md:text-5xl font-bold mb-6 leading-tight" style="color: var(--warm-50);">
                {{ $heading }}
            </h2>

            <p class="font-body text-sm md:text-base mb-10 leading-relaxed max-w-md mx-auto" style="color: var(--warm-400);">
                {{ $subtext ?: "We require {$leadTimeHours} hours notice to ensure everything is baked fresh just for you." }}
            </p>

            <x-storefront.button href="{{ $href }}" variant="primary" size="lg">
                {{ $buttonText }}
            </x-storefront.button>
        </div>
    </div>
</x-storefront.section>
