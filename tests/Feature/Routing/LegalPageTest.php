<?php

use Illuminate\Support\Facades\URL;

test('legal pages use application URLs', function (string $routeName) {
    config(['tenancy.central_domains' => ['kneadit.test']]);
    URL::forceRootUrl('https://kneadit.test');
    URL::forceScheme('https');

    $this->get(route($routeName))
        ->assertOk()
        ->assertSeeHtml('<link rel="icon" href="https://kneadit.test/images/logo-icon.png" type="image/png" />')
        ->assertSeeHtml('<a href="https://kneadit.test" class="nav-brand">KneadIt</a>')
        ->assertSeeHtml('<a href="https://kneadit.test#features">Features</a>')
        ->assertSeeHtml('<a href="https://kneadit.test#pricing">Pricing</a>')
        ->assertSeeHtml('<a href="https://kneadit.test#contact">Contact</a>')
        ->assertSeeHtml('<a href="https://kneadit.test/register" class="nav-cta">Get Started</a>')
        ->assertSeeHtml('<a href="https://kneadit.test/privacy">Privacy</a>')
        ->assertSeeHtml('<a href="https://kneadit.test/terms">Terms</a>');
})->with(['terms', 'privacy']);
