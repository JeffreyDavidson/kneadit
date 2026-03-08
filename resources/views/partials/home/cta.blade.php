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
<section class="py-24 px-4 text-center" style="background: linear-gradient(135deg, var(--warm-800), var(--warm-900));">
    <div class="max-w-3xl mx-auto">
        <p class="font-script text-2xl mb-4" style="color: var(--warm-500);">What are you waiting for?</p>
        <h2 class="font-display text-4xl md:text-5xl font-bold mb-6" style="color: var(--warm-100);">
            {{ $heading }}
        </h2>
        <p class="text-lg mb-10" style="color: var(--warm-400);">
            {{ $subtext ?: "Place your order and taste the difference that passion and craftsmanship make. We require {$leadTimeHours} hours notice to ensure the highest quality." }}
        </p>
        <a href="{{ $href }}" class="inline-block text-lg px-12 py-5 rounded-full font-bold transition-all duration-300 hover:scale-105 hover:shadow-2xl" style="background: var(--warm-500); color: var(--warm-900);">
            {{ $buttonText }}
        </a>
    </div>
</section>
