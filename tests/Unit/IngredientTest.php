<?php

namespace Tests\Unit;

use App\Models\Ingredient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IngredientTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.connections.central' => config('database.connections.sqlite')]);
        $tenantMigrationPath = database_path('migrations/tenant');
        if (is_dir($tenantMigrationPath)) {
            $this->artisan('migrate', ['--path' => $tenantMigrationPath, '--realpath' => true]);
        }
    }

    /** @test */
    public function adjust_stock_creates_stock_adjustment_record(): void
    {
        $ingredient = Ingredient::create([
            'name' => 'Flour',
            'unit' => 'kg',
            'current_stock' => 50,
            'low_stock_threshold' => 10,
            'cost_per_unit' => 2.50,
        ]);

        $ingredient->adjustStock(10, 'purchase', 'Restocked');

        $this->assertDatabaseHas('stock_adjustments', [
            'ingredient_id' => $ingredient->id,
            'quantity' => 10,
            'type' => 'purchase',
            'notes' => 'Restocked',
        ]);
    }

    /** @test */
    public function adjust_stock_updates_current_stock(): void
    {
        $ingredient = Ingredient::create([
            'name' => 'Sugar',
            'unit' => 'kg',
            'current_stock' => 20,
            'low_stock_threshold' => 5,
            'cost_per_unit' => 1.50,
        ]);

        $ingredient->adjustStock(-5, 'usage', 'Order usage');

        $this->assertEquals(15, $ingredient->fresh()->current_stock);
    }

    /** @test */
    public function stock_can_go_below_zero(): void
    {
        $ingredient = Ingredient::create([
            'name' => 'Butter',
            'unit' => 'kg',
            'current_stock' => 2,
            'low_stock_threshold' => 5,
            'cost_per_unit' => 4.00,
        ]);

        $ingredient->adjustStock(-5, 'usage', 'Over-used');

        $this->assertEquals(-3, (float) $ingredient->fresh()->current_stock);
    }

    /** @test */
    public function low_stock_threshold_detection(): void
    {
        $ingredient = Ingredient::create([
            'name' => 'Eggs',
            'unit' => 'dozen',
            'current_stock' => 8,
            'low_stock_threshold' => 10,
            'cost_per_unit' => 3.00,
        ]);

        $this->assertTrue($ingredient->isLowStock());
        $this->assertFalse($ingredient->isOutOfStock());
        $this->assertEquals('low', $ingredient->getStockStatus());
    }

    /** @test */
    public function out_of_stock_detection(): void
    {
        $ingredient = Ingredient::create([
            'name' => 'Vanilla',
            'unit' => 'ml',
            'current_stock' => 0,
            'low_stock_threshold' => 50,
            'cost_per_unit' => 0.10,
        ]);

        $this->assertTrue($ingredient->isOutOfStock());
        $this->assertEquals('out', $ingredient->getStockStatus());
    }

    /** @test */
    public function good_stock_status(): void
    {
        $ingredient = Ingredient::create([
            'name' => 'Milk',
            'unit' => 'liters',
            'current_stock' => 100,
            'low_stock_threshold' => 10,
            'cost_per_unit' => 1.00,
        ]);

        $this->assertFalse($ingredient->isLowStock());
        $this->assertFalse($ingredient->isOutOfStock());
        $this->assertEquals('good', $ingredient->getStockStatus());
    }

    /** @test */
    public function cost_per_unit_is_stored_correctly(): void
    {
        $ingredient = Ingredient::create([
            'name' => 'Cocoa',
            'unit' => 'kg',
            'current_stock' => 10,
            'low_stock_threshold' => 2,
            'cost_per_unit' => 12.75,
        ]);

        $this->assertEquals(12.75, (float) $ingredient->cost_per_unit);
    }
}
