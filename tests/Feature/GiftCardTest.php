<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureStorefrontEnabled;
use App\Http\Middleware\TrackPageView;
use App\Models\GiftCard;
use App\Services\GiftCardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomainOrSubdomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use Tests\TestCase;

class GiftCardTest extends TestCase
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
    public function gift_cards_page_loads(): void
    {
        $response = $this->withoutMiddleware([InitializeTenancyByDomainOrSubdomain::class, PreventAccessFromCentralDomains::class, EnsureStorefrontEnabled::class, TrackPageView::class])->get('/gift-cards');
        $response->assertOk();
    }

    /** @test */
    public function gift_card_can_be_purchased_with_valid_data(): void
    {
        $response = $this->withoutMiddleware([InitializeTenancyByDomainOrSubdomain::class, PreventAccessFromCentralDomains::class, EnsureStorefrontEnabled::class, TrackPageView::class])->postJson('/gift-cards/purchase', [
            'purchaser_name' => 'John Doe',
            'purchaser_email' => 'john@example.com',
            'recipient_name' => 'Jane Doe',
            'recipient_email' => 'jane@example.com',
            'message' => 'Happy Birthday!',
            'initial_balance' => 50.00,
        ]);

        $response->assertOk();
        $response->assertJsonStructure(['success', 'gift_card' => ['code', 'balance']]);
        $this->assertDatabaseHas('gift_cards', [
            'purchaser_email' => 'john@example.com',
            'initial_balance' => 50.00,
        ]);
    }

    /** @test */
    public function gift_card_balance_check_works(): void
    {
        $service = new GiftCardService;
        $card = $service->create([
            'purchaser_name' => 'John',
            'purchaser_email' => 'john@example.com',
            'initial_balance' => 25.00,
        ]);

        $response = $this->withoutMiddleware([InitializeTenancyByDomainOrSubdomain::class, PreventAccessFromCentralDomains::class, EnsureStorefrontEnabled::class, TrackPageView::class])->postJson('/gift-cards/balance', [
            'code' => $card->code,
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true, 'current_balance' => 25.00]);
    }

    /** @test */
    public function gift_card_balance_check_returns_404_for_invalid_code(): void
    {
        $response = $this->withoutMiddleware([InitializeTenancyByDomainOrSubdomain::class, PreventAccessFromCentralDomains::class, EnsureStorefrontEnabled::class, TrackPageView::class])->postJson('/gift-cards/balance', [
            'code' => 'INVALID-CODE-HERE',
        ]);

        $response->assertStatus(404);
        $response->assertJson(['success' => false]);
    }

    /** @test */
    public function gift_card_code_is_generated_in_correct_format(): void
    {
        $service = new GiftCardService;
        $code = $service->generateCode();

        // Format: XXXX-XXXX-XXXX-XXXX
        $this->assertMatchesRegularExpression('/^[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}$/', $code);
    }

    /** @test */
    public function gift_card_redemption_deducts_balance(): void
    {
        $service = new GiftCardService;
        $card = $service->create([
            'purchaser_name' => 'John',
            'purchaser_email' => 'john@example.com',
            'initial_balance' => 100.00,
        ]);

        $result = $service->redeem($card->code, 30.00);

        $this->assertTrue($result['success']);
        $this->assertEquals(30.00, $result['amount_applied']);
        $this->assertEquals(70.00, $result['remaining_balance']);
    }

    /** @test */
    public function depleted_gift_card_cannot_be_redeemed(): void
    {
        $service = new GiftCardService;
        $card = $service->create([
            'purchaser_name' => 'John',
            'purchaser_email' => 'john@example.com',
            'initial_balance' => 10.00,
        ]);

        // Fully deplete
        $service->redeem($card->code, 10.00);

        $result = $service->redeem($card->code, 5.00);
        $this->assertFalse($result['success']);
    }

    /** @test */
    public function expired_gift_card_cannot_be_redeemed(): void
    {
        $card = GiftCard::create([
            'code' => 'EXPD-TEST-CODE-1234',
            'initial_balance' => 50.00,
            'current_balance' => 50.00,
            'purchaser_name' => 'John',
            'purchaser_email' => 'john@example.com',
            'is_active' => true,
            'expires_at' => now()->subDay(),
        ]);

        $service = new GiftCardService;
        $result = $service->redeem($card->code, 10.00);
        $this->assertFalse($result['success']);
    }

    /** @test */
    public function gift_card_purchase_validates_required_fields(): void
    {
        $response = $this->withoutMiddleware([InitializeTenancyByDomainOrSubdomain::class, PreventAccessFromCentralDomains::class, EnsureStorefrontEnabled::class, TrackPageView::class])->postJson('/gift-cards/purchase', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['purchaser_name', 'purchaser_email', 'initial_balance']);
    }
}
