<?php

use App\Models\Financial\Expense;
use App\Models\Staff\User;
use Illuminate\Support\Facades\Storage;
use Laravel\Pennant\Feature;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\withoutMiddleware;

beforeEach(function () {
    setUpTenantTest();
    Storage::fake('receipts');
    Storage::fake('public');
    Feature::define('growth-features', fn () => true);
    test()->png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==');
});

test('a manager can open a receipt from the private receipts disk', function () {
    Storage::disk('receipts')->put('private.png', test()->png);
    $expense = Expense::factory()->create(['receipt_image' => 'private.png']);
    actingAs(User::factory()->manager()->create());

    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('admin.expenses.receipt', $expense, false));

    $response->assertOk()
        ->assertHeader('Content-Type', 'image/png');
    expect($response->streamedContent())->toBe(test()->png);
});

test('a receipt saved on the public disk before receipts were private is still served', function () {
    Storage::disk('public')->put('receipts/legacy.png', test()->png);
    $expense = Expense::factory()->create(['receipt_image' => 'receipts/legacy.png']);
    actingAs(User::factory()->manager()->create());

    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('admin.expenses.receipt', $expense, false));

    $response->assertOk();
    expect($response->streamedContent())->toBe(test()->png);
});

test('the private receipts disk wins when the same path exists on both disks', function () {
    Storage::disk('receipts')->put('same.png', test()->png);
    Storage::disk('public')->put('same.png', 'public copy');
    $expense = Expense::factory()->create(['receipt_image' => 'same.png']);
    actingAs(User::factory()->manager()->create());

    $response = withoutMiddleware(tenantMiddleware())
        ->get(route('admin.expenses.receipt', $expense, false));

    expect($response->streamedContent())->toBe(test()->png);
});

test('staff below manager cannot open a receipt', function () {
    Storage::disk('receipts')->put('private.png', test()->png);
    $expense = Expense::factory()->create(['receipt_image' => 'private.png']);
    actingAs(User::factory()->staff()->create());

    withoutMiddleware(tenantMiddleware())
        ->get(route('admin.expenses.receipt', $expense, false))
        ->assertForbidden();
});

test('a bakery without the growth plan feature cannot open a receipt', function () {
    Feature::define('growth-features', fn () => false);
    Storage::disk('receipts')->put('private.png', test()->png);
    $expense = Expense::factory()->create(['receipt_image' => 'private.png']);
    actingAs(User::factory()->owner()->create());

    withoutMiddleware(tenantMiddleware())
        ->get(route('admin.expenses.receipt', $expense, false))
        ->assertForbidden();
});

test('guests are sent to the login page', function () {
    Storage::disk('receipts')->put('private.png', test()->png);
    $expense = Expense::factory()->create(['receipt_image' => 'private.png']);

    withoutMiddleware(tenantMiddleware())
        ->get(route('admin.expenses.receipt', $expense, false))
        ->assertRedirect(route('login'));
});

test('an expense without a receipt returns 404', function () {
    $expense = Expense::factory()->create(['receipt_image' => null]);
    actingAs(User::factory()->manager()->create());

    withoutMiddleware(tenantMiddleware())
        ->get(route('admin.expenses.receipt', $expense, false))
        ->assertNotFound();
});

test('a receipt whose file is gone returns 404', function () {
    $expense = Expense::factory()->create(['receipt_image' => 'missing.png']);
    actingAs(User::factory()->manager()->create());

    withoutMiddleware(tenantMiddleware())
        ->get(route('admin.expenses.receipt', $expense, false))
        ->assertNotFound();
});
