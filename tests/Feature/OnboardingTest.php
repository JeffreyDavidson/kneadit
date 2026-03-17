<?php

namespace Tests\Feature;

use App\Filament\Pages\Onboarding;
use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;
use Tests\TestCase;

class OnboardingTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Override central connection to use the same in-memory DB
        config(['database.connections.central' => config('database.connections.sqlite')]);

        // Run tenant migrations against the test DB so we have all tables
        $tenantMigrationPath = database_path('migrations/tenant');
        if (is_dir($tenantMigrationPath)) {
            $this->artisan('migrate', [
                '--path' => $tenantMigrationPath,
                '--realpath' => true,
            ]);
        }

        $this->user = User::create([
            'name' => 'Test Baker',
            'email' => 'test@testbakery.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);
    }

    /** @test */
    public function onboarding_page_is_registered_in_filament(): void
    {
        // Verify the page class exists and has correct properties
        $page = new Onboarding;
        $this->assertEquals('Welcome to KneadIt', $page::$title ?? (new \ReflectionClass($page))->getStaticPropertyValue('title'));
        $this->assertFalse($page::$shouldRegisterNavigation ?? (new \ReflectionClass($page))->getStaticPropertyValue('shouldRegisterNavigation'));
    }

    /** @test */
    public function completed_onboarding_is_detected(): void
    {
        $this->assertNull(Setting::get('onboarding_completed_at'));

        Setting::set('onboarding_completed_at', now()->toISOString());

        $this->assertNotNull(Setting::get('onboarding_completed_at'));
    }

    /** @test */
    public function welcome_step_saves_bakery_name_and_owner(): void
    {
        $page = new Onboarding;
        $page->bakery_name = 'Sweet Sunrise Bakery';
        $page->owner_name = 'Jane Baker';

        $reflection = new \ReflectionMethod($page, 'saveWelcomeStep');
        $reflection->invoke($page);

        $this->assertEquals('Sweet Sunrise Bakery', Setting::get('store_name'));
    }

    /** @test */
    public function contact_step_saves_all_contact_info(): void
    {
        $page = new Onboarding;
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
        $page = new Onboarding;
        $page->brand_color_primary = '#ff5500';
        $page->brand_color_secondary = '#00aaff';
        $page->store_logo = [];

        $reflection = new \ReflectionMethod($page, 'saveBrandingStep');
        $reflection->invoke($page);

        $this->assertEquals('#ff5500', Setting::get('brand_color_primary'));
        $this->assertEquals('#00aaff', Setting::get('brand_color_secondary'));
    }

    /** @test */
    public function product_step_creates_product_in_database(): void
    {
        $category = Category::create([
            'name' => 'Breads',
            'slug' => 'breads',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $page = new Onboarding;
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
    public function product_step_generates_slug_from_name(): void
    {
        $category = Category::create([
            'name' => 'Cakes',
            'slug' => 'cakes',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $page = new Onboarding;
        $page->product_name = 'Double Chocolate Layer Cake';
        $page->product_description = '';
        $page->product_price = '35.00';
        $page->product_category_id = (string) $category->id;

        $reflection = new \ReflectionMethod($page, 'saveProductStep');
        $reflection->invoke($page);

        $this->assertDatabaseHas('products', [
            'slug' => 'double-chocolate-layer-cake',
        ]);
    }

    /** @test */
    public function business_hours_step_saves_open_days_only(): void
    {
        $page = new Onboarding;
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
        $this->assertArrayNotHasKey('thursday', $hours);
        $this->assertArrayHasKey('friday', $hours);
        $this->assertArrayNotHasKey('saturday', $hours);
        $this->assertArrayNotHasKey('sunday', $hours);

        $this->assertEquals('08:00', $hours['monday']['open']);
        $this->assertEquals('17:00', $hours['monday']['close']);
        $this->assertEquals('09:00', $hours['friday']['open']);
        $this->assertEquals('15:00', $hours['friday']['close']);
    }

    /** @test */
    public function business_hours_with_no_days_saves_empty_schedule(): void
    {
        $page = new Onboarding;
        $page->hours_monday = false;
        $page->hours_tuesday = false;
        $page->hours_wednesday = false;
        $page->hours_thursday = false;
        $page->hours_friday = false;
        $page->hours_saturday = false;
        $page->hours_sunday = false;

        $reflection = new \ReflectionMethod($page, 'saveBusinessHoursStep');
        $reflection->invoke($page);

        $hours = json_decode(Setting::get('operating_hours'), true);
        $this->assertEmpty($hours);
    }

    /** @test */
    public function compliance_step_saves_state_and_details(): void
    {
        $page = new Onboarding;
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
    public function compliance_step_without_license_saves_empty(): void
    {
        $page = new Onboarding;
        $page->cottage_food_state = 'TX';
        $page->revenue_cap = '50000';
        $page->license_number = '';
        $page->allergy_disclaimer = 'Please ask about allergens.';
        $page->compliance_acknowledged = true;

        $reflection = new \ReflectionMethod($page, 'saveComplianceStep');
        $reflection->invoke($page);

        $this->assertEquals('TX', Setting::get('cottage_food_state'));
        $this->assertEquals('', Setting::get('license_number'));
    }

    /** @test */
    public function delivery_step_saves_all_delivery_settings(): void
    {
        $page = new Onboarding;
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
    public function delivery_disabled_still_saves_pickup_settings(): void
    {
        $page = new Onboarding;
        $page->delivery_enabled = false;
        $page->delivery_radius = '';
        $page->delivery_fee = '';
        $page->free_delivery_over = false;
        $page->free_delivery_threshold = '';
        $page->delivery_minimum_order = '';
        $page->pickup_enabled = true;
        $page->pickup_instructions = 'Come to the back door.';

        $reflection = new \ReflectionMethod($page, 'saveDeliveryStep');
        $reflection->invoke($page);

        $this->assertEquals('0', Setting::get('delivery_enabled'));
        $this->assertEquals('1', Setting::get('pickup_enabled'));
        $this->assertEquals('Come to the back door.', Setting::get('pickup_instructions'));
    }

    /** @test */
    public function free_delivery_threshold_cleared_when_disabled(): void
    {
        $page = new Onboarding;
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
    }

    /** @test */
    public function payment_step_saves_paypal_credentials(): void
    {
        $page = new Onboarding;
        $page->payment_methods = ['paypal', 'cash'];
        $page->paypal_client_id = 'AaBbCcDdEeFf123456';
        $page->paypal_client_secret = 'secret_xyz_789';
        $page->paypal_sandbox = true;

        $reflection = new \ReflectionMethod($page, 'savePaymentStep');
        $reflection->invoke($page);

        $this->assertEquals('AaBbCcDdEeFf123456', Setting::get('paypal_client_id'));
        $this->assertEquals('secret_xyz_789', Setting::get('paypal_client_secret'));
        $this->assertEquals('1', Setting::get('paypal_sandbox'));
        $this->assertEquals('["paypal","cash"]', Setting::get('payment_methods'));
    }

    /** @test */
    public function payment_step_with_live_mode(): void
    {
        $page = new Onboarding;
        $page->payment_methods = ['paypal'];
        $page->paypal_client_id = 'LiveClientId';
        $page->paypal_client_secret = 'LiveSecret';
        $page->paypal_sandbox = false;

        $reflection = new \ReflectionMethod($page, 'savePaymentStep');
        $reflection->invoke($page);

        $this->assertEquals('0', Setting::get('paypal_sandbox'));
    }

    /** @test */
    public function payment_step_with_cash_only(): void
    {
        $page = new Onboarding;
        $page->payment_methods = ['cash'];
        $page->paypal_client_id = '';
        $page->paypal_client_secret = '';
        $page->paypal_sandbox = true;

        $reflection = new \ReflectionMethod($page, 'savePaymentStep');
        $reflection->invoke($page);

        $this->assertEquals('["cash"]', Setting::get('payment_methods'));
        $this->assertEquals('cash', Setting::get('payment_method'));
        // PayPal credentials should not be saved when paypal not in payment_methods
        $this->assertNull(Setting::get('paypal_client_id'));
    }

    /** @test */
    public function complete_onboarding_sets_timestamp(): void
    {
        $this->assertNull(Setting::get('onboarding_completed_at'));

        $page = new Onboarding;
        $page->completeOnboarding();

        $this->assertNotNull(Setting::get('onboarding_completed_at'));
    }

    /** @test */
    public function complete_onboarding_timestamp_is_valid_iso_date(): void
    {
        $page = new Onboarding;
        $page->completeOnboarding();

        $timestamp = Setting::get('onboarding_completed_at');
        $parsed = Date::parse($timestamp);
        $this->assertNotNull($parsed);
        $this->assertTrue($parsed->isToday());
    }

    /** @test */
    public function onboarding_page_is_hidden_from_navigation(): void
    {
        // Onboarding should not appear in sidebar
        $this->assertFalse(
            (new \ReflectionClass(Onboarding::class))
                ->getStaticPropertyValue('shouldRegisterNavigation')
        );
    }

    /** @test */
    public function full_onboarding_flow_saves_all_settings(): void
    {
        // Simulate walking through all steps
        $page = new Onboarding;

        // Step 1: Welcome
        $page->bakery_name = 'Sunrise Bakery';
        $page->owner_name = 'Jane Baker';
        (new \ReflectionMethod($page, 'saveWelcomeStep'))->invoke($page);

        // Step 2: Contact
        $page->contact_email = 'jane@sunrisebakery.com';
        $page->contact_phone = '555-0100';
        $page->contact_address = '100 Main St, Orlando, FL 32801';
        (new \ReflectionMethod($page, 'saveContactStep'))->invoke($page);

        // Step 3: Branding
        $page->brand_color_primary = '#8b5e3c';
        $page->brand_color_secondary = '#f0c75e';
        $page->store_logo = [];
        (new \ReflectionMethod($page, 'saveBrandingStep'))->invoke($page);

        // Step 4: Product
        $category = Category::create([
            'name' => 'Cookies',
            'slug' => 'cookies',
            'is_active' => true,
            'sort_order' => 1,
        ]);
        $page->product_name = 'Chocolate Chip Cookie';
        $page->product_description = 'Classic and delicious';
        $page->product_price = '3.50';
        $page->product_category_id = (string) $category->id;
        (new \ReflectionMethod($page, 'saveProductStep'))->invoke($page);

        // Step 5: Business Hours
        $page->hours_monday = true;
        $page->hours_monday_open = '07:00';
        $page->hours_monday_close = '18:00';
        $page->hours_tuesday = true;
        $page->hours_tuesday_open = '07:00';
        $page->hours_tuesday_close = '18:00';
        $page->hours_wednesday = true;
        $page->hours_wednesday_open = '07:00';
        $page->hours_wednesday_close = '18:00';
        $page->hours_thursday = true;
        $page->hours_thursday_open = '07:00';
        $page->hours_thursday_close = '18:00';
        $page->hours_friday = true;
        $page->hours_friday_open = '07:00';
        $page->hours_friday_close = '18:00';
        $page->hours_saturday = false;
        $page->hours_sunday = false;
        (new \ReflectionMethod($page, 'saveBusinessHoursStep'))->invoke($page);

        // Step 6: Compliance
        $page->cottage_food_state = 'FL';
        $page->revenue_cap = '250000';
        $page->license_number = '';
        $page->allergy_disclaimer = 'Contains wheat, eggs, dairy.';
        $page->compliance_acknowledged = true;
        (new \ReflectionMethod($page, 'saveComplianceStep'))->invoke($page);

        // Step 7: Delivery
        $page->delivery_enabled = true;
        $page->delivery_radius = '20';
        $page->delivery_fee = '5.00';
        $page->free_delivery_over = true;
        $page->free_delivery_threshold = '50.00';
        $page->delivery_minimum_order = '15.00';
        $page->pickup_enabled = true;
        $page->pickup_instructions = 'Text when you arrive.';
        (new \ReflectionMethod($page, 'saveDeliveryStep'))->invoke($page);

        // Step 8: Payment
        $page->payment_methods = ['paypal', 'cash'];
        $page->paypal_client_id = 'test_client_id';
        $page->paypal_client_secret = 'test_secret';
        $page->paypal_sandbox = true;
        (new \ReflectionMethod($page, 'savePaymentStep'))->invoke($page);

        // Step 10: Complete
        $page->completeOnboarding();

        // Verify everything was saved
        $this->assertEquals('Sunrise Bakery', Setting::get('store_name'));
        $this->assertEquals('jane@sunrisebakery.com', Setting::get('store_email'));
        $this->assertEquals('555-0100', Setting::get('store_phone'));
        $this->assertEquals('#8b5e3c', Setting::get('brand_color_primary'));
        $this->assertEquals('FL', Setting::get('cottage_food_state'));
        $this->assertEquals('1', Setting::get('delivery_enabled'));
        $this->assertEquals('test_client_id', Setting::get('paypal_client_id'));
        $this->assertNotNull(Setting::get('onboarding_completed_at'));

        $this->assertDatabaseHas('products', [
            'name' => 'Chocolate Chip Cookie',
            'price' => 3.50,
        ]);

        $hours = json_decode(Setting::get('operating_hours'), true);
        $this->assertCount(5, $hours); // Mon-Fri
    }
}
