<?php

use App\Models\Platform\Tenant;
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

test('a bakery name is filtered through a data attribute, never built into a script expression', function () {
    Tenant::withoutEvents(fn (): Tenant => Tenant::factory()->create(['store_name' => "Evil'+alert(document.cookie)+'"]));

    $response = get(route('directory'))->assertOk();

    preg_match_all('/x-show="([^"]*)"/', $response->getContent(), $expressions);
    $scripts = implode(' ', $expressions[1]);

    expect($expressions[1])->not->toBeEmpty()
        ->and($scripts)->not->toContain('alert')
        ->and($scripts)->not->toContain('evil');
    $response->assertSeeHtml('data-name="evil&#039;+alert(document.cookie)+&#039;"');
});

test('a stored brand color that is not a hex color renders as the default', function (string $stored) {
    Tenant::withoutEvents(fn (): Tenant => Tenant::factory()->create(['brand_color_primary' => $stored]));

    get(route('directory'))
        ->assertOk()
        ->assertDontSeeHtml('display:none')
        ->assertSeeHtml('background: #d4920c');
})->with([
    'css break-out' => 'red;}body{display:none',
    'named color' => 'red',
    'short hex' => '#fff',
]);

test('a valid brand color is used for the card accents', function () {
    Tenant::withoutEvents(fn (): Tenant => Tenant::factory()->create(['brand_color_primary' => '#336699']));

    get(route('directory'))
        ->assertOk()
        ->assertSeeHtml('background: #336699');
});
