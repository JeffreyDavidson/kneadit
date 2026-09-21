<?php

use Illuminate\Support\Facades\URL;

test('pricing metadata uses the pricing route URL', function () {
    config(['tenancy.central_domains' => ['kneadit.test']]);
    URL::forceRootUrl('https://kneadit.test');
    URL::forceScheme('https');

    $response = $this->get(route('pricing'));

    $response->assertOk()->assertSeeHtml('<meta property="og:url" content="https://kneadit.test/pricing" />')->assertSeeHtml('<link rel="canonical" href="https://kneadit.test/pricing" />')->assertSeeHtml('<link rel="icon" href="https://kneadit.test/images/logo-icon.png" type="image/png" />')->assertSeeHtml('href="https://kneadit.test" class="nav-logo"')->assertSeeHtml('src="https://kneadit.test/images/logo-transparent.png" alt="KneadIt"')
        ->assertSeeHtml('<a href="https://kneadit.test#features">Features</a>')
        ->assertSeeHtml('<a href="https://kneadit.test/resources">Resources</a>')
        ->assertSeeHtml('<a href="https://kneadit.test/changelog">Changelog</a>')
        ->assertDontSee('https://getkneadit.app/pricing');
});
