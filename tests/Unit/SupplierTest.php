<?php

namespace Tests\Unit;

use App\Models\Ingredient;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierTest extends TestCase
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
        User::create(['name' => 'Test', 'email' => 'test@test.com', 'password' => bcrypt('password')]);
    }

    /** @test */
    public function supplier_has_ingredients_relationship(): void
    {
        $supplier = Supplier::create(['name' => 'Flour Co', 'is_active' => true]);
        $ingredient = Ingredient::create(['name' => 'Flour', 'unit' => 'kg', 'current_stock' => 100, 'low_stock_threshold' => 10, 'cost_per_unit' => 1.50]);

        $supplier->ingredients()->attach($ingredient->id, ['unit_price' => 1.20]);

        $this->assertCount(1, $supplier->fresh()->ingredients);
        $this->assertEquals('Flour', $supplier->fresh()->ingredients->first()->name);
    }

    /** @test */
    public function ingredients_pivot_has_unit_price(): void
    {
        $supplier = Supplier::create(['name' => 'Flour Co', 'is_active' => true]);
        $ingredient = Ingredient::create(['name' => 'Flour', 'unit' => 'kg', 'current_stock' => 100, 'low_stock_threshold' => 10, 'cost_per_unit' => 1.50]);

        $supplier->ingredients()->attach($ingredient->id, ['unit_price' => 1.20]);

        $pivot = $supplier->fresh()->ingredients->first()->pivot;
        $this->assertEquals(1.20, $pivot->unit_price);
    }

    /** @test */
    public function is_active_is_cast_to_boolean(): void
    {
        $supplier = Supplier::create(['name' => 'Flour Co', 'is_active' => true]);

        $this->assertIsBool($supplier->is_active);
        $this->assertTrue($supplier->is_active);
    }

    /** @test */
    public function supplier_can_be_deactivated(): void
    {
        $supplier = Supplier::create(['name' => 'Flour Co', 'is_active' => true]);
        $supplier->update(['is_active' => false]);

        $this->assertFalse($supplier->fresh()->is_active);
    }
}
