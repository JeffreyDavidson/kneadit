<?php

namespace Tests\Feature\Central;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\CentralTestCase;

class ExportControllerTest extends CentralTestCase
{
    private function createAdmin(): User
    {
        return User::factory()->create(['role' => 'platform_admin']);
    }

    private function insertTenant(string $id = 'test-bakery'): string
    {
        DB::table('tenants')->insert([
            'id' => $id,
            'name' => 'Test Owner',
            'email' => 'test@example.com',
            'plan' => 'starter',
            'data' => '{}',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $id;
    }

    public function test_unauthenticated_request_returns_403(): void
    {
        $id = $this->insertTenant();
        $response = $this->get("/admin/export/{$id}/products");
        $response->assertForbidden();
    }

    public function test_invalid_export_type_returns_404(): void
    {
        $id = $this->insertTenant();
        $response = $this->actingAs($this->createAdmin())
            ->get("/admin/export/{$id}/invalid");
        $response->assertNotFound();
    }

    public function test_products_csv_export_returns_correct_content_type(): void
    {
        $id = $this->insertTenant();
        $response = $this->actingAs($this->createAdmin())
            ->get("/admin/export/{$id}/products");
        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));
    }

    public function test_categories_csv_export_works(): void
    {
        $id = $this->insertTenant();
        $response = $this->actingAs($this->createAdmin())
            ->get("/admin/export/{$id}/categories");
        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));
    }

    public function test_orders_csv_export_works(): void
    {
        $id = $this->insertTenant();
        $response = $this->actingAs($this->createAdmin())
            ->get("/admin/export/{$id}/orders");
        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));
    }

    public function test_customers_csv_export_works(): void
    {
        $id = $this->insertTenant();
        $response = $this->actingAs($this->createAdmin())
            ->get("/admin/export/{$id}/customers");
        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));
    }

    public function test_reviews_csv_export_works(): void
    {
        $id = $this->insertTenant();
        $response = $this->actingAs($this->createAdmin())
            ->get("/admin/export/{$id}/reviews");
        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));
    }

    public function test_all_zip_export_returns_zip_content_type(): void
    {
        $id = $this->insertTenant();
        $response = $this->actingAs($this->createAdmin())
            ->get("/admin/export/{$id}/all");
        $response->assertOk();
        $this->assertStringContainsString('application/zip', $response->headers->get('content-type'));
    }
}
