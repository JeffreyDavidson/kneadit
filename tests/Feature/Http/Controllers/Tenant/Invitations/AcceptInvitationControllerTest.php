<?php

use App\Models\Staff\StaffInvitation;
use App\Models\Staff\User;

use function Pest\Laravel\withoutMiddleware;

beforeEach(fn () => setUpTenantTest());

test('it accepts a pending invitation and redirects to admin', function () {
    $invitation = StaffInvitation::factory()->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->post(route('invitation.accept', $invitation->token, false), [
            'name' => 'New Staff',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

    $response->assertRedirect('/admin');
});

test('a new user must supply a name and password', function (array $input, array $errors, ?string $oldName) {
    $invitation = StaffInvitation::factory()->create();

    $response = withoutMiddleware(tenantMiddleware())
        ->from("/invite/{$invitation->token}")
        ->post(route('invitation.accept', $invitation->token, false), $input);

    $response->assertRedirect("/invite/{$invitation->token}")
        ->assertSessionHasErrors($errors);

    if ($oldName !== null) {
        $response->assertSessionHasInput('name', $oldName);
    }

    expect(User::query()->where('email', $invitation->email)->exists())->toBeFalse()
        ->and($invitation->fresh()->accepted_at)->toBeNull();
})->with([
    'nothing' => [[], ['name', 'password'], null],
    'name only' => [['name' => 'New Staff'], ['password'], 'New Staff'],
    'password only' => [['password' => 'password123', 'password_confirmation' => 'password123'], ['name'], null],
]);

test('an existing user can accept an invitation without a name or password', function () {
    $user = User::factory()->staff()->create();
    $invitation = StaffInvitation::factory()->create(['email' => $user->email, 'role' => 'manager']);

    $response = withoutMiddleware(tenantMiddleware())
        ->post(route('invitation.accept', $invitation->token, false));

    $response->assertRedirect('/admin');

    expect($invitation->fresh()->accepted_at)->not->toBeNull();
});
