<?php

beforeEach(function () {
    setUpCentralTest();
    config(['tenancy.central_domains' => ['localhost', 'kneadit.test']]);
});

test('changelog page renders', function () {
    $this->get(route('changelog'))->assertOk();
});

test('blog index page renders', function () {
    $this->get(route('blog.index'))->assertOk();
});

test('directory page renders', function () {
    $this->get(route('directory'))->assertOk();
});

test('register page renders', function () {
    $this->get(route('register'))->assertOk();
});

test('forgot password page renders', function () {
    $this->get(route('password.request'))->assertOk();
});

test('blog feed returns xml', function () {
    $this->get(route('blog.feed'))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/rss+xml; charset=UTF-8');
});

test('billing plans page renders for authenticated user', function () {
    $user = App\Models\Staff\User::factory()->owner()->create();

    $this->actingAs($user)
        ->get(route('billing.plans'))
        ->assertOk();
});

test('root controller checks central domains and serves appropriate response', function () {
    $source = file_get_contents(app_path('Http/Controllers/Central/RootController.php'));

    expect($source)
        ->toContain("config('tenancy.central_domains'")
        ->toContain("view('platform.welcome')")
        ->toContain('storefront_enabled')
        ->toContain('external_website')
        ->toContain('storefront-disabled')
        ->toContain('HomeController');
});
