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
<section class="relative py-28 px-4 overflow-hidden" style="background: var(--warm-900);">
    <!-- Subtle warm glow -->
    <div class="absolute inset-0" style="background: radial-gradient(ellipse at center, rgba(212, 146, 12, 0.08), transparent 70%);"></div>

    <div class="relative z-10 max-w-2xl mx-auto text-center">
        <div class="flex items-center justify-center gap-4 mb-8">
            <span class="block w-16 h-px" style="background: var(--warm-500); opacity: 0.3;"></span>
            <span class="block w-2 h-2 rounded-full" style="background: var(--warm-500); opacity: 0.4;"></span>
            <span class="block w-16 h-px" style="background: var(--warm-500); opacity: 0.3;"></span>
        </div>
        <h2 class="font-display text-3xl md:text-5xl font-bold mb-4 leading-tight" style="color: var(--warm-100);">
            Ready to order?
        </h2>
        <p class="font-script text-xl md:text-2xl mb-6" style="color: var(--warm-400);">We'd love to bake for you.</p>
        <p class="text-sm mb-10 max-w-md mx-auto leading-relaxed" style="color: var(--warm-400); opacity: 0.7;">
            {{ $subtext ?: "We require {$leadTimeHours} hours notice to ensure everything is baked fresh just for you." }}
        </p>
        <a href="{{ $href }}" class="inline-block px-12 py-5 rounded-full font-bold transition-all duration-300 hover:scale-105 hover:shadow-2xl" style="background: var(--warm-500); color: var(--warm-900); font-size: 1.05rem;">
            {{ $buttonText }}
        </a>
    </div>
</section>
