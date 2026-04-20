<?php

use App\Filament\Resources\ContactMessages\Pages\ListContactMessages;
use App\Models\Customers\ContactMessage;
use App\Models\Staff\User;
use Filament\Actions\CreateAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
});

test('can list contact messages in the table', function () {
    $messages = ContactMessage::factory()->count(3)->create();

    Livewire::test(ListContactMessages::class)
        ->assertCanSeeTableRecords($messages);
});

test('can create a contact message via slide-over', function () {
    Livewire::test(ListContactMessages::class)
        ->callAction(CreateAction::class, data: [
            'name' => 'Jane Customer',
            'email' => 'jane@example.com',
            'subject' => 'Custom cake inquiry',
            'message' => 'I would like to order a custom cake.',
        ])
        ->assertHasNoFormErrors();

    test()->assertDatabaseHas(ContactMessage::class, [
        'name' => 'Jane Customer',
        'subject' => 'Custom cake inquiry',
    ]);
});

test('can edit a contact message via table action', function () {
    $message = ContactMessage::factory()->create();

    Livewire::test(ListContactMessages::class)
        ->callAction(TestAction::make('edit')->table($message), data: [
            'name' => $message->name,
            'email' => $message->email,
            'subject' => 'Updated subject',
            'message' => $message->message,
        ])
        ->assertHasNoFormErrors();

    expect($message->fresh()->subject)->toBe('Updated subject');
});

test('create contact message validates required fields', function (array $data, array $errors) {
    Livewire::test(ListContactMessages::class)
        ->callAction(CreateAction::class, data: [
            'name' => 'Test',
            'email' => 'test@example.com',
            'subject' => 'Test',
            'message' => 'Test message',
            ...$data,
        ])
        ->assertHasFormErrors($errors);
})->with([
    'name is required' => [['name' => null], ['name' => 'required']],
    'email is required' => [['email' => null], ['email' => 'required']],
    'subject is required' => [['subject' => null], ['subject' => 'required']],
    'message is required' => [['message' => null], ['message' => 'required']],
]);

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
