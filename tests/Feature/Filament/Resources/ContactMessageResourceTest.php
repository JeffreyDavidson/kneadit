<?php

use App\Filament\Resources\ContactMessages\Pages\ListContactMessages;
use App\Filament\Resources\ContactMessages\Pages\ViewContactMessage;
use App\Mail\Customers\ContactMessageReplyMail;
use App\Models\Customers\ContactMessage;
use App\Models\Customers\ContactMessageReply;
use App\Models\Staff\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->user = User::factory()->owner()->create();
    test()->actingAs(test()->user);
});

test('can list contact messages in the table', function () {
    $messages = ContactMessage::factory()->count(3)->create();

    Livewire::test(ListContactMessages::class)
        ->assertCanSeeTableRecords($messages);
});

test('mark-read action toggles is_read on the row', function () {
    $message = ContactMessage::factory()->unread()->create();

    Livewire::test(ListContactMessages::class)
        ->callAction(TestAction::make('toggleRead')->table($message));

    expect($message->refresh()->is_read)->toBeTrue();

    Livewire::test(ListContactMessages::class)
        ->callAction(TestAction::make('toggleRead')->table($message));

    expect($message->refresh()->is_read)->toBeFalse();
});

test('view page renders for a contact message', function () {
    $message = ContactMessage::factory()->create();

    Livewire::test(ViewContactMessage::class, ['record' => $message->getRouteKey()])
        ->assertOk();
});

test('mark-read action on the view page toggles is_read', function () {
    $message = ContactMessage::factory()->unread()->create();

    Livewire::test(ViewContactMessage::class, ['record' => $message->getRouteKey()])
        ->callAction('toggleRead');

    expect($message->refresh()->is_read)->toBeTrue();
});

test('reply action exists on the view page', function () {
    $message = ContactMessage::factory()->create([
        'email' => 'jane@example.com',
        'subject' => 'Custom cake',
    ]);

    Livewire::test(ViewContactMessage::class, ['record' => $message->getRouteKey()])
        ->assertActionExists('reply')
        ->assertActionVisible('reply');
});

test('reply action sends the email, persists the reply, and marks the message as read', function () {
    Mail::fake();

    $message = ContactMessage::factory()->unread()->create([
        'name' => 'Jane Customer',
        'email' => 'jane@example.com',
        'subject' => 'Custom cake',
    ]);

    Livewire::test(ViewContactMessage::class, ['record' => $message->getRouteKey()])
        ->callAction('reply', data: [
            'subject' => 'Re: Custom cake',
            'body' => 'Thanks for reaching out — happy to help with that.',
        ]);

    Mail::assertQueued(ContactMessageReplyMail::class, function (ContactMessageReplyMail $mail) use ($message): bool {
        return $mail->hasTo($message->email)
            && $mail->replySubject === 'Re: Custom cake'
            && str_contains($mail->replyBody, 'Thanks for reaching out');
    });

    expect($message->refresh()->is_read)->toBeTrue();

    $reply = $message->replies()->sole();

    expect($reply)
        ->subject->toBe('Re: Custom cake')
        ->body->toContain('Thanks for reaching out')
        ->user_id->toBe(test()->user->id)
        ->and($reply->sent_at)->not->toBeNull();
});

test('view page renders prior replies as a thread', function () {
    $message = ContactMessage::factory()->create();

    ContactMessageReply::factory()->for($message, 'contactMessage')->recycle(test()->user)->create([
        'subject' => 'Re: First touch',
        'body' => 'Original staff response body that should appear in the thread.',
    ]);

    Livewire::test(ViewContactMessage::class, ['record' => $message->getRouteKey()])
        ->assertOk()
        ->assertSee('Re: First touch')
        ->assertSee('Original staff response body that should appear in the thread.');
});

test('can render contact message table columns', function (string $column) {
    ContactMessage::factory()->create();

    Livewire::test(ListContactMessages::class)
        ->assertCanRenderTableColumn($column);
})->with(['name', 'email', 'subject']);

test('can search contact messages by name', function () {
    $target = ContactMessage::factory()->create(['name' => 'Alice']);
    $other = ContactMessage::factory()->create(['name' => 'Bob']);

    Livewire::test(ListContactMessages::class)
        ->searchTable('Alice')
        ->assertCanSeeTableRecords(collect([$target]))
        ->assertCanNotSeeTableRecords(collect([$other]));
});

test('can sort contact messages by name', function () {
    $alice = ContactMessage::factory()->create(['name' => 'Alice']);
    $zach = ContactMessage::factory()->create(['name' => 'Zach']);

    Livewire::test(ListContactMessages::class)
        ->sortTable('name')
        ->assertCanSeeTableRecords(collect([$alice, $zach]), inOrder: true)
        ->sortTable('name', 'desc')
        ->assertCanSeeTableRecords(collect([$zach, $alice]), inOrder: true);
});

test('can filter contact messages by read status', function () {
    $read = ContactMessage::factory()->read()->create();
    $unread = ContactMessage::factory()->unread()->create();

    Livewire::test(ListContactMessages::class)
        ->filterTable('is_read', true)
        ->assertCanSeeTableRecords(collect([$read]))
        ->assertCanNotSeeTableRecords(collect([$unread]));
});

test('resource returns globally searchable attributes', function () {
    expect(App\Filament\Resources\ContactMessages\ContactMessageResource::getGloballySearchableAttributes())
        ->toBe(['name', 'email', 'subject']);
});

test('resource returns global search result title', function () {
    $message = ContactMessage::factory()->create(['subject' => 'Custom Cake Order']);

    expect(App\Filament\Resources\ContactMessages\ContactMessageResource::getGlobalSearchResultTitle($message))
        ->toBe('Custom Cake Order');
});

test('resource returns global search result details', function () {
    $message = ContactMessage::factory()->create([
        'name' => 'Jane Baker',
        'email' => 'jane@example.com',
        'is_read' => false,
    ]);

    $details = App\Filament\Resources\ContactMessages\ContactMessageResource::getGlobalSearchResultDetails($message);

    expect($details)
        ->toHaveKey('From', 'Jane Baker')
        ->toHaveKey('Email', 'jane@example.com')
        ->toHaveKey('Read', 'No');
});
