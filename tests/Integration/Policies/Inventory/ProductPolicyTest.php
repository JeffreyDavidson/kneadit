<?php

use App\Models\Engagement\Review;
use App\Models\Inventory\Product;
use App\Models\Orders\OrderItem;
use App\Models\Staff\User;
use App\Policies\Inventory\ProductPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

dataset('productManagerRoles', ['staff', 'manager', 'owner']);

test('anyone who manages products can delete a product with no orders or reviews', function (string $role) {
    $user = User::factory()->{$role}()->create();
    $product = Product::factory()->create();

    expect((new ProductPolicy)->delete($user, $product))->toBeTrue()
        ->and(Gate::forUser($user)->allows('delete', $product))->toBeTrue();
})->with('productManagerRoles');

test('nobody can delete a product that has past orders', function (string $role) {
    $user = User::factory()->{$role}()->create();
    $product = Product::factory()->create(['name' => 'Sourdough Loaf']);
    OrderItem::factory()->for($product)->create();

    $response = (new ProductPolicy)->delete($user, $product);

    expect($response->allowed())->toBeFalse()
        ->and($response->message())->toContain('Sourdough Loaf')
        ->and($response->message())->toContain('Deactivate')
        ->and(Gate::forUser($user)->allows('delete', $product))->toBeFalse();
})->with('productManagerRoles');

test('nobody can delete a product that has reviews, because a review keeps no copy of the product name', function (string $role) {
    $user = User::factory()->{$role}()->create();
    $product = Product::factory()->create();
    Review::factory()->for($product)->create();

    expect(Gate::forUser($user)->allows('delete', $product))->toBeFalse();
})->with('productManagerRoles');
