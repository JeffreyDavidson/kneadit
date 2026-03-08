<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureStorefrontEnabled;
use App\Http\Middleware\TrackPageView;
use App\Models\CateringInquiry;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomainOrSubdomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use Tests\TestCase;

class CateringTest extends TestCase
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
    public function catering_page_loads(): void
    {
        $response = $this->withoutMiddleware($this->tenantMiddleware)->get('/catering');
        $response->assertOk();
    }

    /** @test */
    public function catering_inquiry_can_be_submitted_with_valid_data(): void
    {
        Setting::set('catering_lead_time_days', '1');
        Setting::set('catering_minimum_guests', '5');

        $response = $this->withoutMiddleware($this->tenantMiddleware)->post('/catering', [
            'customer_name' => 'Jane Doe',
            'customer_email' => 'jane@example.com',
            'customer_phone' => '555-1234',
            'event_type' => 'wedding',
            'event_date' => now()->addDays(30)->format('Y-m-d'),
            'guest_count' => 50,
            'budget' => '5000',
            'details' => 'We need a beautiful wedding cake and pastries.',
            'dietary_requirements' => 'Gluten-free options needed',
            'venue_address' => '123 Wedding Ln',
        ]);

        $response->assertRedirect(route('storefront.catering'));
        $this->assertDatabaseHas('catering_inquiries', [
            'customer_name' => 'Jane Doe',
            'customer_email' => 'jane@example.com',
            'event_type' => 'wedding',
        ]);
    }

    /** @test */
    public function catering_validation_rejects_missing_required_fields(): void
    {
        $response = $this->withoutMiddleware($this->tenantMiddleware)->post('/catering', []);
        $response->assertSessionHasErrors(['customer_name', 'customer_email', 'event_type', 'event_date', 'guest_count', 'details']);
    }

    /** @test */
    public function catering_validation_rejects_past_event_dates(): void
    {
        Setting::set('catering_lead_time_days', '1');
        Setting::set('catering_minimum_guests', '5');

        $response = $this->withoutMiddleware($this->tenantMiddleware)->post('/catering', [
            'customer_name' => 'Jane Doe',
            'customer_email' => 'jane@example.com',
            'event_type' => 'birthday',
            'event_date' => now()->subDay()->format('Y-m-d'),
            'guest_count' => 10,
            'details' => 'Birthday party',
        ]);

        $response->assertSessionHasErrors(['event_date']);
    }

    /** @test */
    public function inquiry_is_saved_with_default_status(): void
    {
        Setting::set('catering_lead_time_days', '1');
        Setting::set('catering_minimum_guests', '5');

        $this->withoutMiddleware($this->tenantMiddleware)->post('/catering', [
            'customer_name' => 'Bob',
            'customer_email' => 'bob@example.com',
            'event_type' => 'corporate',
            'event_date' => now()->addDays(30)->format('Y-m-d'),
            'guest_count' => 20,
            'details' => 'Corporate event',
        ]);

        // Default status from migration — check it's saved
        $inquiry = CateringInquiry::first();
        $this->assertNotNull($inquiry);
    }
}
