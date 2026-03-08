<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\GiftCard;
use App\Models\GiftCardTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GiftCardStorefrontTest extends TestCase
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

    private function makeCard(array $overrides = []): GiftCard
    {
        static $counter = 0;
        $counter++;

        return GiftCard::create(array_merge([
            'code' => 'GIFT-TEST-' . str_pad($counter, 4, '0', STR_PAD_LEFT),
            'initial_balance' => 50.00,
            'current_balance' => 50.00,
            'purchaser_name' => 'John Doe',
            'purchaser_email' => 'john@example.com',
            'is_active' => true,
        ], $overrides));
    }

    public function test_gift_card_model_exists(): void
    {
        $this->assertTrue(class_exists(GiftCard::class));
    }

    public function test_gift_card_can_be_created_with_correct_balance(): void
    {
        $card = $this->makeCard([
            'initial_balance' => 50.00,
            'current_balance' => 50.00,
            'recipient_name' => 'Jane Doe',
            'recipient_email' => 'jane@example.com',
            'message' => 'Happy birthday!',
        ]);

        $this->assertDatabaseHas('gift_cards', ['id' => $card->id]);
        $this->assertEquals(50.00, $card->current_balance);
        $this->assertEquals(50.00, $card->initial_balance);
    }

    public function test_gift_card_is_usable_when_active_with_balance(): void
    {
        $card = $this->makeCard();
        $this->assertTrue($card->isUsable());
    }

    public function test_gift_card_is_not_usable_when_inactive(): void
    {
        $card = $this->makeCard(['is_active' => false]);
        $this->assertFalse($card->isUsable());
    }

    public function test_gift_card_is_not_usable_when_depleted(): void
    {
        $card = $this->makeCard(['current_balance' => 0.00]);
        $this->assertFalse($card->isUsable());
    }

    public function test_gift_card_is_not_usable_when_expired(): void
    {
        $card = $this->makeCard(['expires_at' => now()->subDay()]);
        $this->assertFalse($card->isUsable());
    }

    public function test_gift_card_status_attribute(): void
    {
        $active = $this->makeCard();
        $inactive = $this->makeCard(['is_active' => false]);
        $depleted = $this->makeCard(['current_balance' => 0.00]);
        $expired = $this->makeCard(['expires_at' => now()->subDay()]);

        $this->assertEquals('active', $active->status);
        $this->assertEquals('inactive', $inactive->status);
        $this->assertEquals('depleted', $depleted->status);
        $this->assertEquals('expired', $expired->status);
    }

    public function test_gift_card_has_transactions_relationship(): void
    {
        $card = $this->makeCard();

        GiftCardTransaction::create([
            'gift_card_id' => $card->id,
            'amount' => 50.00,
            'type' => 'purchase',
            'notes' => 'Initial purchase',
            'created_at' => now(),
        ]);

        $this->assertCount(1, $card->transactions);
        $this->assertEquals(50.00, $card->transactions->first()->amount);
    }
}
