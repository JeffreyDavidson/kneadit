<?php

beforeEach(function () {
    setUpCentralTest();
    config(['tenancy.central_domains' => ['localhost', 'kneadit.test']]);
});

test('register page renders for guests', function () {
    $this->get('/register')->assertOk();
});

test('user can register with valid data', function () {
    $this->post('/register', [
        'name' => 'Jane Baker',
        'email' => 'jane@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'bakery_name' => 'Jane\'s Bakery',
        'terms' => true,
    ])->assertRedirect();

    $this->assertDatabaseHas('users', ['email' => 'jane@example.com']);
    $this->assertAuthenticated();
});
