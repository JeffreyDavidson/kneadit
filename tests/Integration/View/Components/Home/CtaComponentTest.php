<?php

use App\View\Components\Home\Cta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('loads configured content and tenant settings', function () {
    settings([
        'hero_image' => 'storefront/hero.jpg',
        'order_lead_time_hours' => 48,
    ]);

    $component = new Cta([
        'heading' => 'Order Something Special',
        'subtext' => 'Made just for you.',
        'button_text' => 'Browse the Menu',
        'button_link' => 'menu',
    ]);

    expect($component->heading)->toBe('Order Something Special')
        ->and($component->subtext)->toBe('Made just for you.')
        ->and($component->buttonText)->toBe('Browse the Menu')
        ->and($component->href)->toBe(route('storefront.menu'))
        ->and($component->leadTimeHours)->toBe(48)
        ->and($component->imageUrl)->toBe(Storage::url('storefront/hero.jpg'));
});

test('uses CTA defaults for unsupported configuration', function () {
    $component = new Cta(['button_link' => 'unsupported']);

    expect($component->heading)->toBe('Treat Yourself Today')
        ->and($component->subtext)->toBeNull()
        ->and($component->buttonText)->toBe('Start Your Order')
        ->and($component->href)->toBe(route('order.create'))
        ->and($component->imageUrl)->toContain('images.unsplash.com');
});
