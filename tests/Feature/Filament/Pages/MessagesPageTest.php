<?php

use App\Enums\Platform\PlatformSenderType;
use App\Filament\Pages\Platform\Messages;
use App\Models\Platform\PlatformMessage;
use App\Models\Platform\Tenant;
use App\Models\Staff\User;
use Illuminate\Support\Facades\DB;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Stancl\Tenancy\Contracts\Tenant as TenantContract;

use function Pest\Livewire\livewire;

beforeEach(function () {
    setUpCentralTest();

    foreach (['bakery-a', 'bakery-b'] as $id) {
        DB::table('tenants')->insert([
            'id' => $id,
            'name' => $id,
            'email' => "{$id}@test.com",
            'plan' => 'pro',
            'store_name' => $id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    app()->instance(TenantContract::class, Tenant::query()->find('bakery-a'));
});

dataset('roles that can open the page', ['owner', 'manager']);

test('messages page can render', function (string $role) {
    test()->actingAs(User::factory()->{$role}()->create());

    livewire(Messages::class)
        ->assertOk();
})->with('roles that can open the page');

test('staff cannot open the messages page', function () {
    test()->actingAs(User::factory()->staff()->create());

    expect(Messages::canAccess())->toBeFalse();

    livewire(Messages::class)
        ->assertForbidden();
});

test('a manager can open the messages page', function () {
    test()->actingAs(User::factory()->manager()->create());

    expect(Messages::canAccess())->toBeTrue();
});

test('the inbox lists only the current bakery messages', function () {
    test()->actingAs(User::factory()->owner()->create());
    $own = PlatformMessage::factory()->create(['tenant_id' => 'bakery-a', 'subject' => 'Our own notice']);
    $other = PlatformMessage::factory()->create(['tenant_id' => 'bakery-b', 'subject' => 'Their private notice']);

    livewire(Messages::class)
        ->assertSee($own->subject)
        ->assertDontSee($other->subject);
});

test('another bakery message cannot be opened or marked read', function () {
    test()->actingAs(User::factory()->owner()->create());
    $other = PlatformMessage::factory()->create([
        'tenant_id' => 'bakery-b',
        'subject' => 'Their private subject',
        'body' => 'Their private body',
    ]);

    livewire(Messages::class)
        ->call('viewThread', $other->id)
        ->assertSet('viewingMessage', null)
        ->assertDontSee('Their private subject')
        ->assertDontSee('Their private body');

    expect($other->refresh()->is_read)->toBeFalse();
});

test('opening an own message shows it and marks it read', function () {
    test()->actingAs(User::factory()->owner()->create());
    $own = PlatformMessage::factory()->create(['tenant_id' => 'bakery-a', 'subject' => 'Our own subject']);

    livewire(Messages::class)
        ->call('viewThread', $own->id)
        ->assertSet('viewingMessage', $own->id)
        ->assertSee('Our own subject');

    expect($own->refresh()->is_read)->toBeTrue();
});

test('the viewed message cannot be set from the browser', function () {
    test()->actingAs(User::factory()->owner()->create());
    $other = PlatformMessage::factory()->create(['tenant_id' => 'bakery-b']);

    livewire(Messages::class)
        ->set('viewingMessage', $other->id);
})->throws(CannotUpdateLockedPropertyException::class);

test('a reply is stored for the current bakery', function () {
    test()->actingAs(User::factory()->owner()->create());
    $own = PlatformMessage::factory()->create(['tenant_id' => 'bakery-a', 'subject' => 'Question']);

    livewire(Messages::class)
        ->call('viewThread', $own->id)
        ->set('replyBody', 'Thanks, noted')
        ->call('sendReply')
        ->assertHasNoErrors()
        ->assertSet('replyBody', '');

    $reply = PlatformMessage::query()->where('parent_id', $own->id)->sole();

    expect($reply->tenant_id)->toBe('bakery-a')
        ->and($reply->sender_type)->toBe(PlatformSenderType::Tenant)
        ->and($reply->subject)->toBe('Re: Question')
        ->and($reply->body)->toBe('Thanks, noted');
});

test('an empty reply is rejected with a validation error', function () {
    test()->actingAs(User::factory()->owner()->create());
    $own = PlatformMessage::factory()->create(['tenant_id' => 'bakery-a']);

    livewire(Messages::class)
        ->call('viewThread', $own->id)
        ->set('replyBody', '')
        ->call('sendReply')
        ->assertHasErrors(['replyBody' => 'required']);
});

test('replying to another bakery message is refused', function () {
    test()->actingAs(User::factory()->owner()->create());
    $other = PlatformMessage::factory()->create(['tenant_id' => 'bakery-b']);

    livewire(Messages::class)
        ->call('viewThread', $other->id)
        ->set('replyBody', 'Hello')
        ->call('sendReply');

    expect(PlatformMessage::query()->where('parent_id', $other->id)->exists())->toBeFalse();
});

test('the navigation badge counts only the current bakery unread admin messages', function () {
    test()->actingAs(User::factory()->owner()->create());
    PlatformMessage::factory()->count(2)->create(['tenant_id' => 'bakery-a']);
    PlatformMessage::factory()->count(3)->create(['tenant_id' => 'bakery-b']);

    expect(Messages::getNavigationBadge())->toBe('2');
});
