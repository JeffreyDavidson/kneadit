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
