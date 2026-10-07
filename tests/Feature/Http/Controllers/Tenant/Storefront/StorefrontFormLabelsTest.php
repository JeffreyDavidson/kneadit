<?php

use function Pest\Laravel\withoutMiddleware;

beforeEach(function () {
    setUpTenantTest();
    settings(['delivery_enabled' => '1']);
});

/**
 * Names of the form controls in the page that a screen reader would announce
 * with no label: not wrapped in a <label>, not the target of a <label for>,
 * and carrying no aria-label or aria-labelledby.
 *
 * @return list<string>
 */
function unlabelledControls(string $html): array
{
    $document = new DOMDocument;
    $document->loadHTML($html, LIBXML_NOERROR);
    $xpath = new DOMXPath($document);

    $unlabelled = [];
    $controls = $xpath->query('//input[not(@type="hidden" or @type="submit" or @type="button")] | //select | //textarea');

    foreach ($controls as $control) {
        $id = $control->getAttribute('id');
        $isLabelled = $control->getAttribute('aria-label') !== ''
            || $control->getAttribute('aria-labelledby') !== ''
            || $xpath->query('ancestor::label', $control)->length > 0
            || ($id !== '' && $xpath->query("//label[@for='{$id}']")->length > 0);

        if (! $isLabelled) {
            $unlabelled[] = $control->getAttribute('data-test') ?: $control->getAttribute('name') ?: $control->nodeName;
        }
    }

    return $unlabelled;
}

test('every control on a storefront form has a label', function (string $routeName) {
    $response = withoutMiddleware(tenantMiddleware())->get(route($routeName, [], false));

    $response->assertOk();
    expect(unlabelledControls($response->getContent()))->toBeEmpty();
})->with([
    'order form' => 'order.create',
    'catering' => 'storefront.catering',
    'gallery' => 'storefront.gallery',
    'gift cards' => 'storefront.giftCards',
]);

test('label for attributes point at a control that exists exactly once', function (string $routeName) {
    $response = withoutMiddleware(tenantMiddleware())->get(route($routeName, [], false));

    $document = new DOMDocument;
    $document->loadHTML($response->getContent(), LIBXML_NOERROR);
    $xpath = new DOMXPath($document);

    foreach ($xpath->query('//label[@for]') as $label) {
        $target = $label->getAttribute('for');
        expect($xpath->query("//*[@id='{$target}']")->length)->toBe(1, "label for=\"{$target}\"");
    }
})->with([
    'order form' => 'order.create',
    'catering' => 'storefront.catering',
    'gallery' => 'storefront.gallery',
    'gift cards' => 'storefront.giftCards',
]);
