<?php

use Illuminate\Support\Facades\Route;

use function Pest\Laravel\get;

beforeEach(function () {
    setUpCentralTest();

    Route::get('/error-page-test/{status}', fn (int $status) => abort($status))->middleware('web');
});

test('status error pages use the branded stylesheet instead of inline styles', function (int $status, string $heading) {
    $response = get("/error-page-test/{$status}");

    $response->assertStatus($status)->assertSeeHtml(asset('css/errors.css'))->assertSee($heading)->assertDontSeeHtml('<style');
})->with([
    '401' => [401, 'Please sign in'],
    '402' => [402, 'Payment required'],
    '403' => [403, 'That one is off the menu'],
    '419' => [419, 'This link has expired'],
    '429' => [429, 'Easy there, baker'],
]);

test('the expired page tells the visitor to go back and try again', function () {
    get('/error-page-test/419')
        ->assertSee('Your session timed out, please go back and try again.');
});
