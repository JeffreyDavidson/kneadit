<?php

use Illuminate\Support\Facades\URL;

test('robots references the sitemap route for the current application URL', function () {
    config(['tenancy.central_domains' => ['platform.kneadit.test']]);
    URL::forceRootUrl('https://platform.kneadit.test');
    URL::forceScheme('https');

    $response = $this->get(route('robots'));

    $response
        ->assertOk()
        ->assertHeader('Content-Type', 'text/plain; charset=utf-8')
        ->assertContent("User-agent: *\nAllow: /\n\nSitemap: https://platform.kneadit.test/sitemap.xml\n");
});
