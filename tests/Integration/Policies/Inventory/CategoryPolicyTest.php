<?php

use App\Models\Inventory\Category;
use App\Models\Inventory\Product;
use App\Models\Staff\User;
use App\Policies\Inventory\CategoryPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

dataset('categoryManagerRoles', ['manager', 'owner']);

test('managers and owners can delete an empty category', function (string $role) {
    $user = User::factory()->{$role}()->create();
    $category = Category::factory()->create();

    expect((new CategoryPolicy)->delete($user, $category))->toBeTrue()
        ->and(Gate::forUser($user)->allows('delete', $category))->toBeTrue();
})->with('categoryManagerRoles');

test('nobody can delete a category that still has products', function (string $role) {
    $user = User::factory()->{$role}()->create();
    $category = Category::factory()->create(['name' => 'Breads']);
    Product::factory()->for($category)->create();

    $response = (new CategoryPolicy)->delete($user, $category);

    expect($response->allowed())->toBeFalse()
        ->and($response->message())->toContain('Breads')
        ->and($response->message())->toContain('Deactivate')
        ->and(Gate::forUser($user)->allows('delete', $category))->toBeFalse();
})->with('categoryManagerRoles');

test('staff cannot delete categories', function () {
    $staff = User::factory()->staff()->create();

    expect(Gate::forUser($staff)->allows('delete', Category::factory()->create()))->toBeFalse();
});
