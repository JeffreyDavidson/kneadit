<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureStorefrontEnabled;
use App\Http\Middleware\TrackPageView;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomainOrSubdomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use Tests\TestCase;

class ReorderTest extends TestCase
{
    use RefreshDatabase;

    protected array $tenantMiddleware;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.connections.central' => config('database.connections.sqlite')]);
        config(['tenancy.central_domains' => []]);
        $tenantMigrationPath = database_path('migrations/tenant');
        if (is_dir($tenantMigrationPath)) {
            $this->artisan('migrate', ['--path' => $tenantMigrationPath, '--realpath' => true]);
        }
        $this->tenantMiddleware = [
            InitializeTenancyByDomainOrSubdomain::class,
            PreventAccessFromCentralDomains::class,
            EnsureStorefrontEnabled::class,
            TrackPageView::class,
        ];
    }

    /** @test */
    public function reorder_endpoint_returns_order_items_as_json(): void
    {
        $customer = Customer::create(['name' => 'Test', 'email' => 'test@example.com']);
        $category = Category::create(['name' => 'Bread']);
        $product = Product::create(['name' => 'Sourdough', 'price' => 8.50, 'category_id' => $category->id, 'is_active' => true]);

        $order = Order::create([
            'order_number' => 'ORD-RE-001',
            'customer_id' => $customer->id,
            'status' => 'completed',
            'total' => 17.00,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 8.50,
        ]);

        $response = $this->withoutMiddleware($this->tenantMiddleware)
            ->getJson("/order/reorder/{$order->id}");

        $response->assertOk();
        $response->assertJsonStructure(['items' => [['product_id', 'product_name', 'price', 'quantity']]]);
    }

    /** @test */
    public function reorder_data_includes_product_names_and_prices(): void
    {
        $customer = Customer::create(['name' => 'Test', 'email' => 'test@example.com']);
        $category = Category::create(['name' => 'Pastries']);
        $product = Product::create(['name' => 'Croissant', 'price' => 4.00, 'category_id' => $category->id, 'is_active' => true]);

        $order = Order::create([
            'order_number' => 'ORD-RE-002',
            'customer_id' => $customer->id,
            'status' => 'completed',
            'total' => 4.00,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 4.00,
        ]);

        $response = $this->withoutMiddleware($this->tenantMiddleware)
            ->getJson("/order/reorder/{$order->id}");

        $response->assertJsonPath('items.0.product_name', 'Croissant');
        $response->assertJsonPath('items.0.price', '4.00');
    }

    /** @test */
    public function reorder_returns_404_for_nonexistent_order(): void
    {
        $response = $this->withoutMiddleware($this->tenantMiddleware)
            ->getJson('/order/reorder/99999');

        $response->assertNotFound();
    }
}
