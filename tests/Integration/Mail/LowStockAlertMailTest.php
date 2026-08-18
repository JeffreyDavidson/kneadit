<?php

use App\Mail\Operations\LowStockAlertMail;
use App\Models\Inventory\Ingredient;
use Illuminate\Foundation\Testing\RefreshDatabase;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('LowStockAlertMail renders a row for each low-stock ingredient', function () {
    $flour = Ingredient::factory()->create([
        'name' => 'All-purpose flour',
        'unit' => 'lbs',
        'current_stock' => 2,
        'low_stock_threshold' => 10,
    ]);
    $sugar = Ingredient::factory()->create([
        'name' => 'Cane sugar',
        'unit' => 'lbs',
        'current_stock' => 0,
        'low_stock_threshold' => 5,
    ]);

    $rendered = (new LowStockAlertMail(collect([$flour, $sugar])))->render();

    expect($rendered)
        ->toContain('All-purpose flour')
        ->toContain('Cane sugar')
        ->toContain('Threshold: 10')
        ->toContain('Threshold: 5');
});

test('LowStockAlertMail subject names the alert', function () {
    $ingredient = Ingredient::factory()->create(['current_stock' => 0, 'low_stock_threshold' => 1]);

    $mail = new LowStockAlertMail(collect([$ingredient]));

    expect($mail->envelope()->subject)->toBe('Low-stock ingredients — daily alert');
});
