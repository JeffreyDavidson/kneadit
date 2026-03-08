<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\GiftCard;
use App\Models\GiftCardTransaction;
use App\Models\Setting;
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

    public function test_gift_card_model_exists(): void
    {
        $this->assertTrue(class_exists(GiftCard::class));
    }

    public function test_gift_card_can_be_created_with_correct_balance(): void
    {
        $card = GiftCard::create([
            'code' => 'GIFT-TEST-1234',
            'initial_balance' => 50.00,
            'current_balance' => 50.00,
            'purchaser_name' => 'John Doe',
            'purchaser_email' => 'john@example.com',
            'recipient_name' => 'Jane Doe',
            'recipient_email' => 'jane@example.com',
            'message' => 'Happy birthday!',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('gift_cards', ['code' => 'GIFT-TEST-1234']);
        $this->assertEquals(50.00, $card->current_balance);
        $this->assertEquals(50.00, $card->initial_balance);
    }

    public function test_gift_card_is_usable_when_active_with_balance(): void
    {
        $card = GiftCard::create([
            'code' => 'GIFT-USABLE-001',
            'initial_balance' => 25.00,
            'current_balance' => 25.00,
            'is_active' => true,
        ]);

        $this->assertTrue($card->isUsable());
    }

    public function test_gift_card_is_not_usable_when_inactive(): void
    {
        $card = GiftCard::create([
            'code' => 'GIFT-INACTIVE-001',
            'initial_balance' => 25.00,
            'current_balance' => 25.00,
            'is_active' => false,
        ]);

        $this->assertFalse($card->isUsable());
    }

    public function test_gift_card_is_not_usable_when_depleted(): void
    {
        $card = GiftCard::create([
            'code' => 'GIFT-EMPTY-001',
            'initial_balance' => 25.00,
            'current_balance' => 0.00,
            'is_active' => true,
        ]);

        $this->assertFalse($card->isUsable());
    }

    public function test_gift_card_is_not_usable_when_expired(): void
    {
        $card = GiftCard::create([
            'code' => 'GIFT-EXPIRED-001',
            'initial_balance' => 25.00,
            'current_balance' => 25.00,
            'is_active' => true,
            'expires_at' => now()->subDay(),
        ]);

        $this->assertFalse($card->isUsable());
    }

    public function test_gift_card_status_attribute(): void
    {
        $active = GiftCard::create([
            'code' => 'GIFT-STATUS-A',
            'initial_balance' => 25.00,
            'current_balance' => 25.00,
            'is_active' => true,
        ]);

        $inactive = GiftCard::create([
            'code' => 'GIFT-STATUS-I',
            'initial_balance' => 25.00,
            'current_balance' => 25.00,
            'is_active' => false,
        ]);

        $depleted = GiftCard::create([
            'code' => 'GIFT-STATUS-D',
            'initial_balance' => 25.00,
            'current_balance' => 0.00,
            'is_active' => true,
        ]);

        $expired = GiftCard::create([
            'code' => 'GIFT-STATUS-E',
            'initial_balance' => 25.00,
            'current_balance' => 25.00,
            'is_active' => true,
            'expires_at' => now()->subDay(),
        ]);

        $this->assertEquals('active', $active->status);
        $this->assertEquals('inactive', $inactive->status);
        $this->assertEquals('depleted', $depleted->status);
        $this->assertEquals('expired', $expired->status);
    }

    public function test_gift_card_has_transactions_relationship(): void
    {
        $card = GiftCard::create([
            'code' => 'GIFT-TXN-001',
            'initial_balance' => 50.00,
            'current_balance' => 50.00,
            'is_active' => true,
        ]);

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
