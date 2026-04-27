<?php

use Illuminate\Support\Facades\Blade;

test('preview-card renders heading and slot content', function () {
    $html = Blade::render(
        '<x-admin.dashboard.preview-card heading="Recent Orders" icon="🧾">Body content</x-admin.dashboard.preview-card>',
    );

    expect($html)
        ->toContain('class="preview-widget')
        ->toContain('Recent Orders')
        ->toContain('🧾')
        ->toContain('Body content');
});

test('stat-row renders label and value', function () {
    $html = Blade::render(
        '<x-admin.dashboard.stat-row label="Pending" value="12" />',
    );

    expect($html)
        ->toContain('class="pw-stat')
        ->toContain('Pending')
        ->toContain('12');
});

test('list-row renders label, optional value, and optional dot', function () {
    $withDot = Blade::render(
        '<x-admin.dashboard.list-row label="Order #100" value="$28" dot-color="#d4a574" />',
    );
    $withoutDot = Blade::render(
        '<x-admin.dashboard.list-row label="Order #100" />',
    );

    expect($withDot)
        ->toContain('class="pw-row')
        ->toContain('Order #100')
        ->toContain('$28')
        ->toContain('class="pw-dot"')
        ->toContain('#d4a574');

    expect($withoutDot)
        ->toContain('Order #100')
        ->not->toContain('class="pw-dot"');
});

test('list-row falls back to slot content when label is omitted', function () {
    $html = Blade::render(
        '<x-admin.dashboard.list-row value="$28"><a href="/foo">Order #100</a> — Sarah M.</x-admin.dashboard.list-row>',
    );

    expect($html)
        ->toContain('class="pw-row')
        ->toContain('<a href="/foo">Order #100</a>')
        ->toContain('Sarah M.')
        ->toContain('$28');
});

test('bar-row renders label with computed pct fallback or explicit value text', function () {
    $defaultValue = Blade::render(
        '<x-admin.dashboard.bar-row label="Chocolate Cake" pct="85" />',
    );
    $explicitValue = Blade::render(
        '<x-admin.dashboard.bar-row label="Monthly Goal" pct="49" value="$2,450 / $5,000" />',
    );

    expect($defaultValue)
        ->toContain('Chocolate Cake')
        ->toContain('85%')
        ->toContain('width: 85%');

    expect($explicitValue)
        ->toContain('Monthly Goal')
        ->toContain('$2,450 / $5,000')
        ->toContain('width: 49%')
        ->not->toContain('49%</span>');
});

test('spark-bars renders one bar per data point with correct heights', function () {
    $html = Blade::render(
        '<x-admin.dashboard.spark-bars :bars="[20, 35, 60]" :height="32" />',
    );

    expect($html)
        ->toContain('class="pw-line')
        ->toContain('height: 32px')
        ->toContain('height: 20%')
        ->toContain('height: 35%')
        ->toContain('height: 60%')
        ->and(substr_count($html, 'pw-line-bar'))->toBe(3);
});
