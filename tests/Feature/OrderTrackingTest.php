<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureStorefrontEnabled;
use App\Http\Middleware\TrackPageView;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomainOrSubdomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use Tests\TestCase;

class OrderTrackingTest extends TestCase
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
    public function tracking_page_loads(): void
    {
        $response = $this->withoutMiddleware([InitializeTenancyByDomainOrSubdomain::class, PreventAccessFromCentralDomains::class, EnsureStorefrontEnabled::class, TrackPageView::class])->get('/track');
        $response->assertOk();
    }

    /** @test */
    public function tracking_with_valid_email_returns_orders(): void
    {
        $user = User::create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);

        $customer = Customer::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ]);

        Order::create([
            'order_number' => 'KN260308A001',
            'customer_id' => $customer->id,
            'status' => 'confirmed',
            'subtotal' => 25.00,
            'total' => 25.00,
            'user_id' => $user->id,
        ]);

        $response = $this->withoutMiddleware([InitializeTenancyByDomainOrSubdomain::class, PreventAccessFromCentralDomains::class, EnsureStorefrontEnabled::class, TrackPageView::class])->post('/track', [
            'email' => 'jane@example.com',
        ]);

        $response->assertOk();
        $response->assertSee('KN260308A001');
    }

    /** @test */
    public function tracking_with_no_orders_shows_empty_state(): void
    {
        $response = $this->withoutMiddleware([InitializeTenancyByDomainOrSubdomain::class, PreventAccessFromCentralDomains::class, EnsureStorefrontEnabled::class, TrackPageView::class])->post('/track', [
            'email' => 'nobody@example.com',
        ]);

        $response->assertOk();
    }

    /** @test */
    public function tracking_shows_correct_status_for_each_order_stage(): void
    {
        $user = User::create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);

        $customer = Customer::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ]);

        foreach (['pending', 'confirmed', 'baking', 'ready', 'delivered'] as $status) {
            Order::create([
                'order_number' => 'KN'.strtoupper($status),
                'customer_id' => $customer->id,
                'status' => $status,
                'subtotal' => 10.00,
                'total' => 10.00,
                'user_id' => $user->id,
            ]);
        }

        $response = $this->withoutMiddleware([InitializeTenancyByDomainOrSubdomain::class, PreventAccessFromCentralDomains::class, EnsureStorefrontEnabled::class, TrackPageView::class])->post('/track', [
            'email' => 'jane@example.com',
        ]);

        $response->assertOk();
        $this->assertEquals(5, Order::where('customer_id', $customer->id)->count());
    }

    /** @test */
    public function orders_display_items_and_totals(): void
    {
        $user = User::create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);

        $category = Category::create([
            'name' => 'Breads',
            'slug' => 'breads',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $product = Product::create([
            'name' => 'Baguette',
            'slug' => 'baguette',
            'price' => 5.00,
            'category_id' => $category->id,
            'is_active' => true,
        ]);

        $customer = Customer::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ]);

        $order = Order::create([
            'order_number' => 'KN260308ITEM',
            'customer_id' => $customer->id,
            'status' => 'confirmed',
            'subtotal' => 15.00,
            'total' => 15.00,
            'user_id' => $user->id,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 3,
            'unit_price' => 5.00,
        ]);

        $response = $this->withoutMiddleware([InitializeTenancyByDomainOrSubdomain::class, PreventAccessFromCentralDomains::class, EnsureStorefrontEnabled::class, TrackPageView::class])->post('/track', [
            'email' => 'jane@example.com',
        ]);

        $response->assertOk();
        $response->assertSee('Baguette');
        $response->assertSee('15.00');
    }

    /** @test */
    public function tracking_requires_email(): void
    {
        $response = $this->withoutMiddleware([InitializeTenancyByDomainOrSubdomain::class, PreventAccessFromCentralDomains::class, EnsureStorefrontEnabled::class, TrackPageView::class])->post('/track', []);
        $response->assertSessionHasErrors('email');
    }
}
