<?php

namespace Tests\Unit;

use App\Models\Customer;
use App\Models\GiftCard;
use App\Models\Order;
use App\Models\User;
use App\Services\GiftCardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class GiftCardServiceTest extends TestCase
{
    use RefreshDatabase;

    protected GiftCardService $service;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.connections.central' => config('database.connections.sqlite')]);
        $tenantMigrationPath = database_path('migrations/tenant');
        if (is_dir($tenantMigrationPath)) {
            $this->artisan('migrate', ['--path' => $tenantMigrationPath, '--realpath' => true]);
        }

        $this->service = new GiftCardService();
    }

    /** @test */
    public function generate_code_creates_formatted_code(): void
    {
        $code = $this->service->generateCode();

        $this->assertMatchesRegularExpression('/^[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}$/', $code);
    }

    /** @test */
    public function create_generates_unique_codes(): void
    {
        $card1 = $this->service->create([
            'initial_balance' => 50,
            'purchaser_name' => 'Alice',
            'purchaser_email' => 'alice@test.com',
        ]);
        $card2 = $this->service->create([
            'initial_balance' => 25,
            'purchaser_name' => 'Bob',
            'purchaser_email' => 'bob@test.com',
        ]);

        $this->assertNotEquals($card1->code, $card2->code);
    }

    /** @test */
    public function check_balance_returns_correct_card(): void
    {
        $card = $this->service->create([
            'initial_balance' => 100,
            'purchaser_name' => 'Test',
            'purchaser_email' => 'test@test.com',
        ]);

        $found = $this->service->checkBalance($card->code);

        $this->assertNotNull($found);
        $this->assertEquals(100, (float) $found->current_balance);
    }

    /** @test */
    public function redeem_deducts_from_balance(): void
    {
        $card = $this->service->create([
            'initial_balance' => 50,
            'purchaser_name' => 'Test',
            'purchaser_email' => 'test@test.com',
        ]);

        $result = $this->service->redeem($card->code, 20);

        $this->assertTrue($result['success']);
        $this->assertEquals(20, $result['amount_applied']);
        $this->assertEquals(30, $result['remaining_balance']);
    }

    /** @test */
    public function redeem_creates_transaction_record(): void
    {
        $card = $this->service->create([
            'initial_balance' => 50,
            'purchaser_name' => 'Test',
            'purchaser_email' => 'test@test.com',
        ]);

        Mail::fake();
        $user = User::create(['name' => 'Test', 'email' => 'u@t.com', 'password' => bcrypt('p')]);
        $customer = Customer::create(['name' => 'C', 'email' => 'c@t.com']);
        $order = Order::create(['user_id' => $user->id, 'customer_id' => $customer->id, 'status' => 'pending', 'total' => 15, 'subtotal' => 15]);

        $this->service->redeem($card->code, 15, $order->id);

        $this->assertDatabaseHas('gift_card_transactions', [
            'gift_card_id' => $card->id,
            'amount' => -15,
            'type' => 'redemption',
            'order_id' => $order->id,
        ]);
    }

    /** @test */
    public function redeem_caps_at_available_balance(): void
    {
        $card = $this->service->create([
            'initial_balance' => 20,
            'purchaser_name' => 'Test',
            'purchaser_email' => 'test@test.com',
        ]);

        $result = $this->service->redeem($card->code, 50);

        $this->assertTrue($result['success']);
        $this->assertEquals(20, $result['amount_applied']);
        $this->assertEquals(0, $result['remaining_balance']);
    }

    /** @test */
    public function redeem_fails_when_card_inactive(): void
    {
        $card = $this->service->create([
            'initial_balance' => 50,
            'purchaser_name' => 'Test',
            'purchaser_email' => 'test@test.com',
        ]);
        $card->update(['is_active' => false]);

        $result = $this->service->redeem($card->code, 10);

        $this->assertFalse($result['success']);
    }

    /** @test */
    public function redeem_fails_when_card_expired(): void
    {
        $card = $this->service->create([
            'initial_balance' => 50,
            'purchaser_name' => 'Test',
            'purchaser_email' => 'test@test.com',
            'expires_at' => now()->subDay(),
        ]);

        $result = $this->service->redeem($card->code, 10);

        $this->assertFalse($result['success']);
    }
}
