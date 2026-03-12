<?php

namespace Tests\Feature;

use App\Mail\OrderPlaced;
use App\Mail\OrderConfirmed;
use App\Mail\ReviewRequest;
use App\Mail\CateringQuote;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use Tests\CentralTestCase;

class BakerBrandedEmailTest extends CentralTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $user = User::create([
            'name' => 'Baker',
            'email' => 'baker@test.com',
            'password' => bcrypt('password'),
        ]);

        $customer = Customer::create([
            'name' => 'Test Customer',
            'email' => 'customer@test.com',
        ]);

        $this->order = Order::create([
            'order_number' => 'ORD-001',
            'customer_id' => $customer->id,
            'user_id' => $user->id,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'payment_method' => 'cash',
            'subtotal' => 25.00,
            'delivery_fee' => 0,
            'discount' => 0,
            'total' => 25.00,
            'requested_date' => now()->addDays(3),
        ]);
    }

    public function test_order_placed_email_has_baker_branded_from(): void
    {
        Setting::set('store_name', 'Sweet Treats');

        $mail = new OrderPlaced($this->order);
        $envelope = $mail->envelope();

        $this->assertStringContainsString('Sweet Treats via KneadIt', $envelope->from->name);
    }

    public function test_order_placed_email_sends_from_platform_domain(): void
    {
        $mail = new OrderPlaced($this->order);
        $envelope = $mail->envelope();

        $this->assertEquals(config('mail.from.address', 'hello@getkneadit.app'), $envelope->from->address);
    }

    public function test_order_placed_email_has_reply_to_baker(): void
    {
        Setting::set('store_email', 'baker@sweetreats.com');
        Setting::set('store_name', 'Sweet Treats');

        $mail = new OrderPlaced($this->order);
        $envelope = $mail->envelope();

        $replyTos = $envelope->replyTo;
        $this->assertNotEmpty($replyTos);
        $this->assertEquals('baker@sweetreats.com', $replyTos[0]->address);
    }

    public function test_reply_to_is_empty_when_no_store_email(): void
    {
        // No store_email set
        $mail = new OrderPlaced($this->order);
        $envelope = $mail->envelope();

        $this->assertEmpty($envelope->replyTo);
    }

    public function test_from_name_defaults_when_no_store_name(): void
    {
        // No store_name set
        $mail = new OrderPlaced($this->order);
        $envelope = $mail->envelope();

        $this->assertStringContainsString('KneadIt Bakery via KneadIt', $envelope->from->name);
    }

    public function test_order_confirmed_uses_baker_branded_from(): void
    {
        Setting::set('store_name', 'Flour Power');

        $mail = new OrderConfirmed($this->order);
        $envelope = $mail->envelope();

        $this->assertStringContainsString('Flour Power via KneadIt', $envelope->from->name);
    }

    public function test_all_customer_mailables_use_baker_branded_trait(): void
    {
        $mailables = [
            \App\Mail\OrderPlaced::class,
            \App\Mail\OrderConfirmed::class,
            \App\Mail\OrderReady::class,
            \App\Mail\OrderBaking::class,
            \App\Mail\OrderCancelled::class,
            \App\Mail\OrderDelivered::class,
            \App\Mail\ReviewRequest::class,
            \App\Mail\HappyBirthday::class,
            \App\Mail\BirthdayDiscount::class,
            \App\Mail\CustomerBlast::class,
            \App\Mail\ProductAvailable::class,
            \App\Mail\WeeklyDigest::class,
            \App\Mail\CateringQuote::class,
            \App\Mail\NewOrderMessage::class,
            \App\Mail\RepeatOrderReminder::class,
        ];

        foreach ($mailables as $mailable) {
            $uses = class_uses_recursive($mailable);
            $this->assertContains(
                \App\Mail\Concerns\BakerBranded::class,
                $uses,
                "{$mailable} should use BakerBranded trait"
            );
        }
    }
}
