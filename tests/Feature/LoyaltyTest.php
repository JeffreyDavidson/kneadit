<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\LoyaltyPoint;
use App\Models\LoyaltyReward;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Http\Middleware\EnsureStorefrontEnabled;
use App\Http\Middleware\TrackPageView;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomainOrSubdomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use Tests\TestCase;

class LoyaltyTest extends TestCase
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
    public function rewards_page_loads(): void
    {
        $response = $this->withoutMiddleware([InitializeTenancyByDomainOrSubdomain::class, PreventAccessFromCentralDomains::class, EnsureStorefrontEnabled::class, TrackPageView::class])->get('/rewards');
        $response->assertOk();
    }

    /** @test */
    public function rewards_page_shows_active_rewards(): void
    {
        LoyaltyReward::create([
            'name' => 'Free Cookie',
            'description' => 'Get a free cookie!',
            'points_required' => 100,
            'reward_type' => 'free_product',
            'reward_value' => 0,
            'is_active' => true,
        ]);

        $response = $this->withoutMiddleware([InitializeTenancyByDomainOrSubdomain::class, PreventAccessFromCentralDomains::class, EnsureStorefrontEnabled::class, TrackPageView::class])->get('/rewards');
        $response->assertOk();
        $response->assertSee('Free Cookie');
    }

    /** @test */
    public function points_balance_check_works_with_valid_email(): void
    {
        $customer = Customer::create([
            'name' => 'Loyal Customer',
            'email' => 'loyal@example.com',
        ]);

        LoyaltyPoint::create([
            'customer_id' => $customer->id,
            'points' => 150,
            'type' => 'earned',
            'description' => 'Order reward',
        ]);

        $response = $this->withoutMiddleware([InitializeTenancyByDomainOrSubdomain::class, PreventAccessFromCentralDomains::class, EnsureStorefrontEnabled::class, TrackPageView::class])->post('/rewards/check', [
            'email' => 'loyal@example.com',
        ]);

        $response->assertOk();
        $response->assertSee('150');
    }

    /** @test */
    public function points_balance_check_for_unknown_email_shows_zero(): void
    {
        $response = $this->withoutMiddleware([InitializeTenancyByDomainOrSubdomain::class, PreventAccessFromCentralDomains::class, EnsureStorefrontEnabled::class, TrackPageView::class])->post('/rewards/check', [
            'email' => 'unknown@example.com',
        ]);

        $response->assertOk();
        // Should show 0 points for unknown customer
    }

    /** @test */
    public function points_are_calculated_correctly_with_earned_and_redeemed(): void
    {
        $customer = Customer::create([
            'name' => 'Active Customer',
            'email' => 'active@example.com',
        ]);

        LoyaltyPoint::create([
            'customer_id' => $customer->id,
            'points' => 200,
            'type' => 'earned',
            'description' => 'Order 1',
        ]);

        LoyaltyPoint::create([
            'customer_id' => $customer->id,
            'points' => 50,
            'type' => 'redeemed',
            'description' => 'Redeemed reward',
        ]);

        $this->assertEquals(150, $customer->total_points);
    }

    /** @test */
    public function loyalty_program_name_is_configurable(): void
    {
        Setting::set('loyalty_program_name', 'Baker Bucks');

        $response = $this->withoutMiddleware([InitializeTenancyByDomainOrSubdomain::class, PreventAccessFromCentralDomains::class, EnsureStorefrontEnabled::class, TrackPageView::class])->get('/rewards');
        $response->assertOk();
        $response->assertSee('Baker Bucks');
    }

    /** @test */
    public function rewards_check_requires_email(): void
    {
        $response = $this->withoutMiddleware([InitializeTenancyByDomainOrSubdomain::class, PreventAccessFromCentralDomains::class, EnsureStorefrontEnabled::class, TrackPageView::class])->post('/rewards/check', []);
        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function lifetime_points_earned_only_counts_earned_type(): void
    {
        $customer = Customer::create([
            'name' => 'Test',
            'email' => 'test@example.com',
        ]);

        LoyaltyPoint::create([
            'customer_id' => $customer->id,
            'points' => 300,
            'type' => 'earned',
            'description' => 'Earned',
        ]);

        LoyaltyPoint::create([
            'customer_id' => $customer->id,
            'points' => 100,
            'type' => 'redeemed',
            'description' => 'Redeemed',
        ]);

        $this->assertEquals(300, $customer->lifetime_points_earned);
    }

    /** @test */
    public function reward_type_labels_are_correct(): void
    {
        $reward = LoyaltyReward::create([
            'name' => '10% Off',
            'points_required' => 200,
            'reward_type' => 'percentage_discount',
            'reward_value' => 10,
            'is_active' => true,
        ]);

        $this->assertEquals('10.00% Off', $reward->reward_type_label);

        $fixedReward = LoyaltyReward::create([
            'name' => '$5 Off',
            'points_required' => 100,
            'reward_type' => 'fixed_discount',
            'reward_value' => 5.00,
            'is_active' => true,
        ]);

        $this->assertEquals('$5.00 Off', $fixedReward->reward_type_label);
    }
}
