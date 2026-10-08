<?php

use App\Models\Inventory\Ingredient;
use App\Models\Inventory\Recipe;
use App\Models\Inventory\StockAdjustment;
use App\Models\Staff\User;
use App\Policies\Inventory\IngredientPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

dataset('ingredientManagerRoles', ['manager', 'owner']);

test('managers and owners can delete an unused ingredient', function (string $role) {
    $user = User::factory()->{$role}()->create();
    $ingredient = Ingredient::factory()->create();

    expect(Gate::forUser($user)->allows('delete', $ingredient))->toBeTrue();
})->with('ingredientManagerRoles');

test('nobody can delete an ingredient used in a recipe, and the reason says so', function (string $role) {
    $user = User::factory()->{$role}()->create();
    $ingredient = Ingredient::factory()->create(['name' => 'Bread Flour']);
    Recipe::factory()->create()->inventoryIngredients()->attach($ingredient, ['quantity' => 1, 'unit' => $ingredient->unit]);

    $response = (new IngredientPolicy)->delete($user, $ingredient);

    expect($response->allowed())->toBeFalse()
        ->and($response->message())->toContain('Bread Flour is used in recipes');
})->with('ingredientManagerRoles');

test('nobody can delete an ingredient with stock history, and the reason says so', function (string $role) {
    $user = User::factory()->{$role}()->create();
    $ingredient = Ingredient::factory()->create(['name' => 'Bread Flour']);
    StockAdjustment::factory()->for($ingredient)->create();

    $response = (new IngredientPolicy)->delete($user, $ingredient);

    expect($response->allowed())->toBeFalse()
        ->and($response->message())->toContain('Bread Flour has stock history');
})->with('ingredientManagerRoles');

test('staff cannot delete an ingredient', function () {
    $staff = User::factory()->staff()->create();

    expect((new IngredientPolicy)->delete($staff, Ingredient::factory()->create()))->toBeFalse();
});
