<?php

beforeEach(function () {
    setUpCentralTest();
    config(['tenancy.central_domains' => ['localhost', 'kneadit.test']]);
});

test('register endpoint is rate limited', function () {
    for ($i = 0; $i < 6; $i++) {
        $this->post('/register', [
            'name' => 'Test',
            'email' => "test{$i}@example.com",
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'bakery_name' => 'Test Bakery',
            'terms' => true,
        ]);
    }

    $response = $this->post('/register', [
        'name' => 'Test',
        'email' => 'final@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'bakery_name' => 'Test Bakery',
        'terms' => true,
    ]);

    $response->assertStatus(429);
});
