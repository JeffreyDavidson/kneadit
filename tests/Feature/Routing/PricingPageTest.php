<?php

use Illuminate\Support\Facades\URL;

test('pricing metadata uses the pricing route URL', function () {
    config(['tenancy.central_domains' => ['kneadit.test']]);
    URL::forceRootUrl('https://kneadit.test');
    URL::forceScheme('https');

    $response = $this->get(route('pricing'));

    $response
        ->assertOk()
        ->assertSee('<meta property="og:url" content="https://kneadit.test/pricing" />', escape: false)
        ->assertSee('<link rel="canonical" href="https://kneadit.test/pricing" />', escape: false)
        ->assertSeeHtml('<link rel="icon" href="https://kneadit.test/images/logo-icon.png" type="image/png" />')
        ->assertSeeHtml('<img src="https://kneadit.test/images/logo-transparent.png" alt="KneadIt" />')
        ->assertDontSee('https://getkneadit.app/pricing');
});
