<?php

use App\Mail\NewSubscriberNotification;
use App\Mail\WelcomeBaker;

test('welcome baker email has correct subject', function () {
    $mail = new WelcomeBaker(
        bakerName: 'Jane',
        storeName: 'Sunrise Bakery',
        adminUrl: 'https://sunrise.kneadit.test/admin',
        plan: 'starter',
        trialEndsAt: 'April 15, 2026',
    );

    expect($mail->envelope()->subject)->toBe('Welcome to KneadIt — Sunrise Bakery is ready!');
});

test('welcome baker email has correct properties', function () {
    $mail = new WelcomeBaker(
        bakerName: 'Jane',
        storeName: 'Sunrise Bakery',
        adminUrl: 'https://sunrise.kneadit.test/admin',
        plan: 'starter',
        trialEndsAt: 'April 15, 2026',
    );

    expect($mail->bakerName)->toBe('Jane');
    expect($mail->storeName)->toBe('Sunrise Bakery');
    expect($mail->adminUrl)->toBe('https://sunrise.kneadit.test/admin');
    expect($mail->plan)->toBe('starter');
    expect($mail->trialEndsAt)->toBe('April 15, 2026');
});

test('new subscriber notification has correct subject', function () {
    $mail = new NewSubscriberNotification(
        bakerName: 'Jane',
        bakerEmail: 'jane@example.com',
        storeName: 'Sunrise Bakery',
        subdomain: 'sunrise',
        plan: 'starter',
    );

    expect($mail->envelope()->subject)->toBe('New KneadIt Signup — Sunrise Bakery');
});

test('new subscriber notification has correct properties', function () {
    $mail = new NewSubscriberNotification(
        bakerName: 'Jane',
        bakerEmail: 'jane@example.com',
        storeName: 'Sunrise Bakery',
        subdomain: 'sunrise',
        plan: 'starter',
    );

    expect($mail->bakerName)->toBe('Jane');
    expect($mail->bakerEmail)->toBe('jane@example.com');
    expect($mail->storeName)->toBe('Sunrise Bakery');
    expect($mail->subdomain)->toBe('sunrise');
    expect($mail->plan)->toBe('starter');
});
