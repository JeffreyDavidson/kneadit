<?php

use App\Filament\Resources\ContactMessages\Pages\ListContactMessages;
use App\Models\ContactMessage;
use App\Models\User;
use Filament\Actions\CreateAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    $this->actingAs(User::factory()->owner()->create());
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
        ->assertHasNoActionErrors();

    $this->assertDatabaseHas(ContactMessage::class, [
        'name' => 'Jane Customer',
        'subject' => 'Custom cake inquiry',
    ]);
});

test('can edit a contact message via table action', function () {
    $message = ContactMessage::factory()->create();

    Livewire::test(ListContactMessages::class)
        ->callTableAction('edit', $message, data: [
            'name' => $message->name,
            'email' => $message->email,
            'subject' => 'Updated subject',
            'message' => $message->message,
        ])
        ->assertHasNoTableActionErrors();

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
        ->assertHasActionErrors($errors);
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
    $read = ContactMessage::factory()->create(['is_read' => true]);
    $unread = ContactMessage::factory()->create(['is_read' => false]);

    Livewire::test(ListContactMessages::class)
        ->filterTable('is_read', true)
        ->assertCanSeeTableRecords(collect([$read]))
        ->assertCanNotSeeTableRecords(collect([$unread]));
});
