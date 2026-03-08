<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.connections.central' => config('database.connections.sqlite')]);
        $tenantMigrationPath = database_path('migrations/tenant');
        if (is_dir($tenantMigrationPath)) {
            $this->artisan('migrate', ['--path' => $tenantMigrationPath, '--realpath' => true]);
        }
        $this->user = User::create(['name' => 'Baker Bob', 'email' => 'bob@test.com', 'password' => bcrypt('password')]);
        $this->category = Category::create(['name' => 'Bread', 'slug' => 'bread']);
    }

    /** @test */
    public function creating_a_product_logs_created_activity(): void
    {
        $product = Product::create(['name' => 'Sourdough', 'slug' => 'sourdough', 'price' => 5.00, 'category_id' => $this->category->id, 'is_active' => true]);

        $log = ActivityLog::where('model_type', Product::class)->where('model_id', $product->id)->where('action', 'created')->first();

        $this->assertNotNull($log);
        $this->assertStringContainsString('created', $log->description);
    }

    /** @test */
    public function updating_a_product_logs_updated_activity(): void
    {
        $product = Product::create(['name' => 'Sourdough', 'slug' => 'sourdough', 'price' => 5.00, 'category_id' => $this->category->id, 'is_active' => true]);

        $product->update(['price' => 6.00]);

        $log = ActivityLog::where('model_type', Product::class)->where('model_id', $product->id)->where('action', 'updated')->first();

        $this->assertNotNull($log);
    }

    /** @test */
    public function deleting_a_product_logs_deleted_activity(): void
    {
        $product = Product::create(['name' => 'Sourdough', 'slug' => 'sourdough', 'price' => 5.00, 'category_id' => $this->category->id, 'is_active' => true]);
        $productId = $product->id;

        $product->delete();

        $log = ActivityLog::where('model_type', Product::class)->where('model_id', $productId)->where('action', 'deleted')->first();

        $this->assertNotNull($log);
    }

    /** @test */
    public function activity_log_stores_user_name(): void
    {
        $this->actingAs($this->user);

        Product::create(['name' => 'Sourdough', 'slug' => 'sourdough', 'price' => 5.00, 'category_id' => $this->category->id, 'is_active' => true]);

        $log = ActivityLog::where('action', 'created')->where('model_type', Product::class)->latest('id')->first();

        $this->assertEquals('Baker Bob', $log->user_name);
    }

    /** @test */
    public function changes_are_captured_in_properties(): void
    {
        $product = Product::create(['name' => 'Sourdough', 'slug' => 'sourdough', 'price' => 5.00, 'category_id' => $this->category->id, 'is_active' => true]);

        $product->update(['price' => 7.50]);

        $log = ActivityLog::where('action', 'updated')->where('model_type', Product::class)->where('model_id', $product->id)->first();

        $this->assertNotNull($log->properties);
        $this->assertArrayHasKey('changes', $log->properties);
    }

    /** @test */
    public function system_user_name_when_not_authenticated(): void
    {
        Product::create(['name' => 'Sourdough', 'slug' => 'sourdough', 'price' => 5.00, 'category_id' => $this->category->id, 'is_active' => true]);

        $log = ActivityLog::where('action', 'created')->where('model_type', Product::class)->latest('id')->first();

        $this->assertEquals('System', $log->user_name);
    }
}
