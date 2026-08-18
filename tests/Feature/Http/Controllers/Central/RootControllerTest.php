<?php

beforeEach(function () {
    setUpCentralTest();
    config(['tenancy.central_domains' => ['localhost', 'kneadit.test']]);
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
