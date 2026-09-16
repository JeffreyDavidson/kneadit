<?php

use Illuminate\Support\Facades\URL;

use function Pest\Laravel\get;

beforeEach(function () {
    setUpCentralTest();
    config(['tenancy.central_domains' => ['localhost', 'kneadit.test']]);
});

test('directory page renders', function () {
    URL::forceRootUrl('https://kneadit.test');
    URL::forceScheme('https');

    get(route('directory'))
        ->assertOk()
        ->assertSeeHtml('<link rel="icon" href="https://kneadit.test/favicon.svg" type="image/svg+xml" />')
        ->assertSeeHtml('<a href="https://kneadit.test" class="nav-brand">KneadIt</a>')
        ->assertSeeHtml('<a href="https://kneadit.test">Home</a>')
        ->assertSeeHtml('<a href="https://kneadit.test/directory" style="color: var(--honey)">Find a Bakery</a>')
        ->assertSeeHtml('<a href="https://kneadit.test#pricing">Pricing</a>')
        ->assertSeeHtml('<a href="https://kneadit.test#cta" class="nav-cta">Join Waitlist</a>');
});
