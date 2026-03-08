@php
    $heading = $config['heading'] ?? 'Treat Yourself Today';
    $subtext = $config['subtext'] ?? null;
    $buttonText = $config['button_text'] ?? 'Start Your Order';
    $buttonLink = $config['button_link'] ?? 'order';
    $leadTimeHours = \App\Models\Setting::get('order_lead_time_hours', '24');

    $linkMap = [
        'order' => route('order.create'),
        'menu' => route('storefront.menu'),
        'contact' => route('storefront.contact'),
    ];
    $href = $linkMap[$buttonLink] ?? route('order.create');
@endphp
<section class="py-24 px-4" style="background: var(--warm-900);">
    <div class="max-w-2xl mx-auto text-center">
        <div class="section-divider section-divider-dark mb-14"></div>
        <h2 class="font-display text-3xl md:text-5xl font-bold mb-6 leading-tight" style="color: var(--warm-100);">
            Ready to order?
        </h2>
        <p class="font-script text-2xl mb-4" style="color: var(--warm-400);">We'd love to bake for you.</p>
        <p class="text-base mb-10 max-w-lg mx-auto" style="color: var(--warm-400); opacity: 0.8;">
            {{ $subtext ?: "We require {$leadTimeHours} hours notice to ensure everything is baked fresh." }}
        </p>
        <a href="{{ $href }}" class="inline-block text-lg px-12 py-5 rounded-full font-bold transition-all duration-300 hover:scale-105 hover:shadow-2xl" style="background: var(--warm-500); color: var(--warm-900);">
            {{ $buttonText }}
        </a>
    </div>
</section>
