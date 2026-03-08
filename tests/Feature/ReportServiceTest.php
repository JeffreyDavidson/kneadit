<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Income;
use App\Models\Ingredient;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Services\ReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ReportService $service;
    protected Customer $customer;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.connections.central' => config('database.connections.sqlite')]);
        $tenantMigrationPath = database_path('migrations/tenant');
        if (is_dir($tenantMigrationPath)) {
            $this->artisan('migrate', ['--path' => $tenantMigrationPath, '--realpath' => true]);
        }
        User::create(['name' => 'Test', 'email' => 'test@test.com', 'password' => bcrypt('password')]);
        $this->service = new ReportService();
        $this->customer = Customer::create(['name' => 'Jane Doe', 'email' => 'jane@example.com']);
        $this->category = Category::create(['name' => 'Bread', 'slug' => 'bread']);
    }

    /** @test */
    public function service_exists(): void
    {
        $this->assertInstanceOf(ReportService::class, $this->service);
    }

    /** @test */
    public function sales_report_returns_correct_totals(): void
    {
        $this->createPaidOrder(50.00, '2026-03-01');
        $this->createPaidOrder(30.00, '2026-03-02');

        $report = $this->service->salesReport('2026-03-01', '2026-03-31');

        $this->assertEquals(2, $report['totalOrders']);
        $this->assertEquals(80.00, $report['totalRevenue']);
    }

    /** @test */
    public function sales_report_respects_date_range(): void
    {
        $this->createPaidOrder(50.00, '2026-02-15');
        $this->createPaidOrder(30.00, '2026-03-15');

        $report = $this->service->salesReport('2026-03-01', '2026-03-31');

        $this->assertEquals(1, $report['totalOrders']);
        $this->assertEquals(30.00, $report['totalRevenue']);
    }

    /** @test */
    public function customer_report_counts_new_customers(): void
    {
        Customer::create(['name' => 'New Guy', 'email' => 'new@example.com', 'created_at' => '2026-03-15']);

        $report = $this->service->customerReport('2026-03-01', '2026-03-31');

        // At least 1 new customer in range (could include setUp customer)
        $this->assertArrayHasKey('newCustomers', $report);
        $this->assertGreaterThanOrEqual(1, $report['newCustomers']);
    }

    /** @test */
    public function financial_summary_calculates_profit(): void
    {
        $this->createPaidOrder(100.00, '2026-03-15');
        Expense::create(['description' => 'Flour', 'amount' => 30.00, 'category' => 'supplies', 'date' => '2026-03-15', 'deductible_amount' => 30.00]);

        $report = $this->service->financialSummary(2026);

        $this->assertEquals(100.00, $report['totalRevenue']);
        $this->assertEquals(30.00, $report['totalExpenses']);
        $this->assertEquals(70.00, $report['profit']);
    }

    /** @test */
    public function inventory_report_flags_low_stock(): void
    {
        Ingredient::create(['name' => 'Flour', 'unit' => 'kg', 'current_stock' => 5, 'low_stock_threshold' => 10, 'cost_per_unit' => 1.50]);
        Ingredient::create(['name' => 'Sugar', 'unit' => 'kg', 'current_stock' => 50, 'low_stock_threshold' => 10, 'cost_per_unit' => 2.00]);

        $report = $this->service->inventoryReport();

        $this->assertEquals(1, $report['lowStockItems']);
        $flour = collect($report['ingredients'])->firstWhere('name', 'Flour');
        $this->assertTrue($flour['is_low']);
    }

    protected function createPaidOrder(float $total, string $date): Order
    {
        return Order::create([
            'customer_id' => $this->customer->id,
            'status' => 'delivered',
            'payment_status' => 'paid',
            'subtotal' => $total,
            'total' => $total,
            'requested_date' => $date,
        ]);
    }
}
