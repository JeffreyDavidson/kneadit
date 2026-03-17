<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Product;
use App\Models\Recipe;
use App\Models\SeasonalItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;
use Tests\TestCase;

class ProductTest extends TestCase
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
    public function product_belongs_to_category(): void
    {
        $category = Category::create(['name' => 'Cakes', 'slug' => 'cakes']);
        $product = Product::create(['name' => 'Chocolate Cake', 'slug' => 'chocolate-cake', 'price' => 25.00, 'category_id' => $category->id, 'is_active' => true]);

        $this->assertInstanceOf(Category::class, $product->category);
        $this->assertEquals('Cakes', $product->category->name);
    }

    /** @test */
    public function product_has_recipes_relationship(): void
    {
        $category = Category::create(['name' => 'Bread', 'slug' => 'bread']);
        $product = Product::create(['name' => 'Sourdough', 'slug' => 'sourdough', 'price' => 5.00, 'category_id' => $category->id, 'is_active' => true]);

        Recipe::create(['product_id' => $product->id, 'name' => 'Sourdough Recipe', 'instructions' => 'Mix and bake', 'ingredients' => json_encode(['flour', 'water', 'salt'])]);

        $this->assertCount(1, $product->recipes);
    }

    /** @test */
    public function product_has_seasonal_items(): void
    {
        $category = Category::create(['name' => 'Pies', 'slug' => 'pies']);
        $product = Product::create(['name' => 'Pumpkin Pie', 'slug' => 'pumpkin-pie', 'price' => 15.00, 'category_id' => $category->id, 'is_active' => true]);

        SeasonalItem::create([
            'product_id' => $product->id,
            'available_from' => Date::now()->subMonth(),
            'available_until' => Date::now()->addMonth(),
        ]);

        $this->assertCount(1, $product->seasonalItems);
    }

    /** @test */
    public function is_in_season_returns_true_for_current_seasonal_products(): void
    {
        $category = Category::create(['name' => 'Pies', 'slug' => 'pies']);
        $product = Product::create(['name' => 'Pumpkin Pie', 'slug' => 'pumpkin-pie', 'price' => 15.00, 'category_id' => $category->id, 'is_active' => true]);

        SeasonalItem::create([
            'product_id' => $product->id,
            'available_from' => Date::now()->subMonth(),
            'available_until' => Date::now()->addMonth(),
        ]);

        $product->load('seasonalItems');
        $this->assertTrue($product->isInSeason());
    }

    /** @test */
    public function is_in_season_returns_false_for_out_of_season_products(): void
    {
        $category = Category::create(['name' => 'Pies', 'slug' => 'pies']);
        $product = Product::create(['name' => 'Pumpkin Pie', 'slug' => 'pumpkin-pie', 'price' => 15.00, 'category_id' => $category->id, 'is_active' => true]);

        SeasonalItem::create([
            'product_id' => $product->id,
            'available_from' => Date::now()->subMonths(6),
            'available_until' => Date::now()->subMonths(3),
        ]);

        $product->load('seasonalItems');
        $this->assertFalse($product->isInSeason());
    }

    /** @test */
    public function product_with_no_seasonal_entries_is_always_in_season(): void
    {
        $category = Category::create(['name' => 'Bread', 'slug' => 'bread']);
        $product = Product::create(['name' => 'Sourdough', 'slug' => 'sourdough', 'price' => 5.00, 'category_id' => $category->id, 'is_active' => true]);

        $product->load('seasonalItems');
        $this->assertTrue($product->isInSeason());
    }

    /** @test */
    public function product_margin_attribute(): void
    {
        $category = Category::create(['name' => 'Bread', 'slug' => 'bread']);
        $product = Product::create(['name' => 'Sourdough', 'slug' => 'sourdough', 'price' => 10.00, 'cost' => 4.00, 'category_id' => $category->id, 'is_active' => true]);

        $this->assertEquals(60.0, $product->margin);
    }

    /** @test */
    public function product_margin_is_null_without_cost(): void
    {
        $category = Category::create(['name' => 'Bread', 'slug' => 'bread']);
        $product = Product::create(['name' => 'Sourdough', 'slug' => 'sourdough', 'price' => 10.00, 'category_id' => $category->id, 'is_active' => true]);

        $this->assertNull($product->margin);
    }
}
