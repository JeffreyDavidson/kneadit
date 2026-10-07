<?php

use App\Models\Platform\Tenant;
use App\Models\Staff\User;

use function Pest\Laravel\get;

beforeEach(function () {
    setUpCentralTest();
    config(['tenancy.central_domains' => ['localhost', 'kneadit.test']]);

    test()->tenant = Tenant::factory()->create(['id' => 'sunrise', 'user_id' => User::factory()->owner()->create()->id]);
    test()->tenant->createDomain(['domain' => 'sunrise']);
    test()->tenant->run(fn () => settings(['store_name' => 'Sunrise Bakery']));
});

test('an unknown page on a bakery host shows that bakery\'s 404', function () {
    get('http://sunrise.kneadit.test/no-such-page')
        ->assertNotFound()->assertSee('Sunrise Bakery')->assertSeeHtml('<title>Page Not Found | Sunrise Bakery</title>');
});

test('an unknown page on the central host shows the platform 404', function () {
    get('http://kneadit.test/no-such-page')
        ->assertNotFound()
        ->assertDontSee('Sunrise Bakery');
});

test('an unknown host still answers 404', function () {
    get('http://nobody.kneadit.test/no-such-page')->assertNotFound();
});
