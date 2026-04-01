<?php

beforeEach(function () {
    setUpCentralTest();
    config(['tenancy.central_domains' => ['localhost', 'kneadit.test']]);
});

test('changelog page renders', function () {
    $this->get('/changelog')->assertOk();
});

test('blog index page renders', function () {
    $this->get('/resources')->assertOk();
});

test('directory page renders', function () {
    $this->get('/directory')->assertOk();
});

test('register page renders', function () {
    $this->get('/register')->assertOk();
});

test('forgot password page renders', function () {
    $this->get('/forgot-password')->assertOk();
});

test('blog feed returns xml', function () {
    $this->get('/resources/feed.xml')
        ->assertOk()
        ->assertHeader('Content-Type', 'application/rss+xml; charset=UTF-8');
});

test('billing plans page renders for authenticated user', function () {
    $user = App\Models\Staff\User::factory()->owner()->create();

    $this->actingAs($user)
        ->get('/billing/plans')
        ->assertOk();
});
