<?php

namespace Tests\Feature;

use App\Mail\NewSubscriberNotification;
use App\Mail\WelcomeBaker;
use Tests\TestCase;

class WelcomeEmailTest extends TestCase
{
    /** @test */
    public function welcome_baker_email_has_correct_subject(): void
    {
        $mail = new WelcomeBaker(
            bakerName: 'Jane',
            storeName: 'Sunrise Bakery',
            adminUrl: 'https://sunrise.kneadit.test/admin',
            plan: 'starter',
            trialEndsAt: 'April 15, 2026',
        );

        $this->assertEquals('Welcome to KneadIt — Sunrise Bakery is ready!', $mail->envelope()->subject);
    }

    /** @test */
    public function welcome_baker_email_has_correct_properties(): void
    {
        $mail = new WelcomeBaker(
            bakerName: 'Jane',
            storeName: 'Sunrise Bakery',
            adminUrl: 'https://sunrise.kneadit.test/admin',
            plan: 'starter',
            trialEndsAt: 'April 15, 2026',
        );

        $this->assertEquals('Jane', $mail->bakerName);
        $this->assertEquals('Sunrise Bakery', $mail->storeName);
        $this->assertEquals('https://sunrise.kneadit.test/admin', $mail->adminUrl);
        $this->assertEquals('starter', $mail->plan);
        $this->assertEquals('April 15, 2026', $mail->trialEndsAt);
    }

    /** @test */
    public function new_subscriber_notification_has_correct_subject(): void
    {
        $mail = new NewSubscriberNotification(
            bakerName: 'Jane',
            bakerEmail: 'jane@example.com',
            storeName: 'Sunrise Bakery',
            subdomain: 'sunrise',
            plan: 'starter',
        );

        $this->assertEquals('New KneadIt Signup — Sunrise Bakery', $mail->envelope()->subject);
    }

    /** @test */
    public function new_subscriber_notification_has_correct_properties(): void
    {
        $mail = new NewSubscriberNotification(
            bakerName: 'Jane',
            bakerEmail: 'jane@example.com',
            storeName: 'Sunrise Bakery',
            subdomain: 'sunrise',
            plan: 'starter',
        );

        $this->assertEquals('Jane', $mail->bakerName);
        $this->assertEquals('jane@example.com', $mail->bakerEmail);
        $this->assertEquals('Sunrise Bakery', $mail->storeName);
        $this->assertEquals('sunrise', $mail->subdomain);
        $this->assertEquals('starter', $mail->plan);
    }
}
