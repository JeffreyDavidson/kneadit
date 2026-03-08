<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class OnboardingTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create tenant + domain
        $this->tenant = Tenant::create([
            'id' => 'testbakery',
            'name' => 'Test Baker',
            'email' => 'test@testbakery.com',
            'plan' => 'pro',
            'trial_ends_at' => now()->addDays(30),
            'store_name' => 'Test Bakery',
            'brand_color_primary' => '#6b4c3b',
            'brand_color_secondary' => '#d4a574',
            'is_active' => true,
        ]);

        $this->tenant->domains()->create(['domain' => 'testbakery.kneadit.test']);

        // Initialize tenant context and create user
        tenancy()->initialize($this->tenant);

        $this->user = User::create([
            'name' => 'Test Baker',
            'email' => 'test@testbakery.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);
    }

    protected function tearDown(): void
    {
        tenancy()->end();
        parent::tearDown();
    }

    /** @test */
    public function new_tenant_is_redirected_to_onboarding(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/admin');

        $response->assertRedirect('/admin/onboarding');
    }

    /** @test */
    public function onboarding_page_loads_for_new_tenant(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/admin/onboarding');

        $response->assertStatus(200);
        $response->assertSee('Welcome to KneadIt');
    }

    /** @test */
    public function completed_onboarding_skips_redirect(): void
    {
        Setting::set('onboarding_completed_at', now()->toISOString());

        $response = $this->actingAs($this->user)
            ->get('/admin');

        $response->assertStatus(200);
        $response->assertDontSee('Welcome to KneadIt');
    }

    /** @test */
    public function welcome_step_saves_bakery_name(): void
    {
        $this->actingAs($this->user);

        $page = new \App\Filament\Pages\Onboarding();
        $page->bakery_name = 'Sweet Sunrise Bakery';
        $page->owner_name = 'Jane Baker';

        $reflection = new \ReflectionMethod($page, 'saveWelcomeStep');
        $reflection->invoke($page);

        $this->assertEquals('Sweet Sunrise Bakery', Setting::get('store_name'));
        $this->tenant->refresh();
        $this->assertEquals('Sweet Sunrise Bakery', $this->tenant->store_name);
        $this->assertEquals('Jane Baker', $this->tenant->name);
    }

    /** @test */
    public function contact_step_saves_contact_info(): void
    {
        $page = new \App\Filament\Pages\Onboarding();
        $page->contact_email = 'hello@sweetbakery.com';
        $page->contact_phone = '555-123-4567';
        $page->contact_address = '123 Baker St, Tampa, FL 33601';

        $reflection = new \ReflectionMethod($page, 'saveContactStep');
        $reflection->invoke($page);

        $this->assertEquals('hello@sweetbakery.com', Setting::get('store_email'));
        $this->assertEquals('555-123-4567', Setting::get('store_phone'));
        $this->assertEquals('123 Baker St, Tampa, FL 33601', Setting::get('store_address'));
    }

    /** @test */
    public function branding_step_saves_colors(): void
    {
        $this->actingAs($this->user);

        $page = new \App\Filament\Pages\Onboarding();
        $page->brand_color_primary = '#ff5500';
        $page->brand_color_secondary = '#00aaff';
        $page->store_logo = [];

        $reflection = new \ReflectionMethod($page, 'saveBrandingStep');
        $reflection->invoke($page);

        $this->assertEquals('#ff5500', Setting::get('brand_color_primary'));
        $this->assertEquals('#00aaff', Setting::get('brand_color_secondary'));
        $this->tenant->refresh();
        $this->assertEquals('#ff5500', $this->tenant->brand_color_primary);
        $this->assertEquals('#00aaff', $this->tenant->brand_color_secondary);
    }

    /** @test */
    public function product_step_creates_product(): void
    {
        $category = Category::create([
            'name' => 'Breads',
            'slug' => 'breads',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $page = new \App\Filament\Pages\Onboarding();
        $page->product_name = 'Sourdough Loaf';
        $page->product_description = 'A classic sourdough bread';
        $page->product_price = '12.50';
        $page->product_category_id = (string) $category->id;

        $reflection = new \ReflectionMethod($page, 'saveProductStep');
        $reflection->invoke($page);

        $this->assertDatabaseHas('products', [
            'name' => 'Sourdough Loaf',
            'slug' => 'sourdough-loaf',
            'price' => 12.50,
            'category_id' => $category->id,
            'is_active' => true,
        ]);
    }

    /** @test */
    public function business_hours_step_saves_schedule(): void
    {
        $page = new \App\Filament\Pages\Onboarding();
        $page->hours_monday = true;
        $page->hours_monday_open = '08:00';
        $page->hours_monday_close = '17:00';
        $page->hours_tuesday = true;
        $page->hours_tuesday_open = '08:00';
        $page->hours_tuesday_close = '17:00';
        $page->hours_wednesday = false;
        $page->hours_thursday = false;
        $page->hours_friday = true;
        $page->hours_friday_open = '09:00';
        $page->hours_friday_close = '15:00';
        $page->hours_saturday = false;
        $page->hours_sunday = false;

        $reflection = new \ReflectionMethod($page, 'saveBusinessHoursStep');
        $reflection->invoke($page);

        $hours = json_decode(Setting::get('operating_hours'), true);
        $this->assertArrayHasKey('monday', $hours);
        $this->assertArrayHasKey('tuesday', $hours);
        $this->assertArrayNotHasKey('wednesday', $hours);
        $this->assertEquals('08:00', $hours['monday']['open']);
        $this->assertEquals('17:00', $hours['monday']['close']);
        $this->assertEquals('09:00', $hours['friday']['open']);
    }

    /** @test */
    public function compliance_step_saves_state_and_disclaimer(): void
    {
        $page = new \App\Filament\Pages\Onboarding();
        $page->cottage_food_state = 'FL';
        $page->revenue_cap = '250000';
        $page->license_number = 'CF-12345';
        $page->allergy_disclaimer = 'We use nuts and dairy.';
        $page->compliance_acknowledged = true;

        $reflection = new \ReflectionMethod($page, 'saveComplianceStep');
        $reflection->invoke($page);

        $this->assertEquals('FL', Setting::get('cottage_food_state'));
        $this->assertEquals('250000', Setting::get('revenue_cap'));
        $this->assertEquals('CF-12345', Setting::get('license_number'));
        $this->assertEquals('We use nuts and dairy.', Setting::get('allergy_disclaimer'));
        $this->assertEquals('1', Setting::get('compliance_acknowledged'));
    }

    /** @test */
    public function delivery_step_saves_delivery_settings(): void
    {
        $page = new \App\Filament\Pages\Onboarding();
        $page->delivery_enabled = true;
        $page->delivery_radius = '15';
        $page->delivery_fee = '5.00';
        $page->free_delivery_over = true;
        $page->free_delivery_threshold = '50.00';
        $page->delivery_minimum_order = '20.00';
        $page->pickup_enabled = true;
        $page->pickup_instructions = 'Ring the doorbell.';

        $reflection = new \ReflectionMethod($page, 'saveDeliveryStep');
        $reflection->invoke($page);

        $this->assertEquals('1', Setting::get('delivery_enabled'));
        $this->assertEquals('15', Setting::get('delivery_radius'));
        $this->assertEquals('5.00', Setting::get('delivery_fee'));
        $this->assertEquals('50.00', Setting::get('free_delivery_threshold'));
        $this->assertEquals('20.00', Setting::get('delivery_minimum_order'));
        $this->assertEquals('1', Setting::get('pickup_enabled'));
        $this->assertEquals('Ring the doorbell.', Setting::get('pickup_instructions'));
    }

    /** @test */
    public function delivery_step_without_free_delivery_clears_threshold(): void
    {
        $page = new \App\Filament\Pages\Onboarding();
        $page->delivery_enabled = true;
        $page->delivery_radius = '10';
        $page->delivery_fee = '7.00';
        $page->free_delivery_over = false;
        $page->free_delivery_threshold = '50.00'; // should be ignored
        $page->delivery_minimum_order = '';
        $page->pickup_enabled = false;
        $page->pickup_instructions = '';

        $reflection = new \ReflectionMethod($page, 'saveDeliveryStep');
        $reflection->invoke($page);

        $this->assertNull(Setting::get('free_delivery_threshold'));
        $this->assertEquals('0', Setting::get('pickup_enabled'));
    }

    /** @test */
    public function paypal_step_saves_credentials(): void
    {
        $this->actingAs($this->user);

        $page = new \App\Filament\Pages\Onboarding();
        $page->paypal_client_id = 'AaBbCcDdEeFf123456';
        $page->paypal_client_secret = 'secret_xyz_789';
        $page->paypal_sandbox = true;

        $reflection = new \ReflectionMethod($page, 'savePayPalStep');
        $reflection->invoke($page);

        $this->assertEquals('AaBbCcDdEeFf123456', Setting::get('paypal_client_id'));
        $this->assertEquals('secret_xyz_789', Setting::get('paypal_client_secret'));
        $this->assertEquals('1', Setting::get('paypal_sandbox'));
    }

    /** @test */
    public function complete_onboarding_sets_timestamp(): void
    {
        $this->assertNull(Setting::get('onboarding_completed_at'));

        $page = new \App\Filament\Pages\Onboarding();
        $page->completeOnboarding();

        $this->assertNotNull(Setting::get('onboarding_completed_at'));
    }

    /** @test */
    public function onboarding_prefills_from_tenant_data(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/admin/onboarding');

        $response->assertStatus(200);
        // Tenant data should be pre-filled
        $response->assertSee('Test Bakery');
    }

    /** @test */
    public function unauthenticated_user_is_not_redirected_to_onboarding(): void
    {
        $response = $this->get('/admin');

        // Should redirect to login, not onboarding
        $response->assertRedirect();
        $this->assertStringNotContainsString('onboarding', $response->headers->get('Location', ''));
    }
}
