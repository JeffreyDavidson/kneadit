<?php

use App\Http\Middleware\ResolveInvitation;
use App\Models\Staff\StaffInvitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

pest()->use(RefreshDatabase::class);

beforeEach(fn () => setUpTenantTest());

test('it resolves a valid pending invitation onto the request', function () {
    $invitation = StaffInvitation::factory()->create();

    $request = Request::create("/invite/{$invitation->token}");
    $request->setRouteResolver(fn () => (new Illuminate\Routing\Route('GET', 'invite/{token}', []))->bind($request));

    $middleware = new ResolveInvitation;
    $resolved = null;
    $middleware->handle($request, function ($req) use (&$resolved) {
        $resolved = $req->attributes->get('invitation');

        return new Illuminate\Http\Response('ok');
    });

    expect($resolved)->toBeInstanceOf(StaffInvitation::class)
        ->and($resolved->id)->toBe($invitation->id);
});

test('it returns expired view for expired invitation', function () {
    $invitation = StaffInvitation::factory()->create([
        'expires_at' => now()->subDay(),
    ]);

    $request = Request::create("/invite/{$invitation->token}");
    $request->setRouteResolver(fn () => (new Illuminate\Routing\Route('GET', 'invite/{token}', []))->bind($request));

    $middleware = new ResolveInvitation;
    $response = $middleware->handle($request, fn () => new Illuminate\Http\Response('should not reach'));

    expect($response->status())->toBe(200)
        ->and($response->getContent())->toContain('expired');
});
