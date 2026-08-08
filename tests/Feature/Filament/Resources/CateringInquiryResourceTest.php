<?php

use App\Enums\Customers\CateringInquiryStatus;
use App\Filament\Resources\CateringInquiries\CateringInquiryResource;
use App\Filament\Resources\CateringInquiries\Pages\ListCateringInquiries;
use App\Models\Customers\CateringInquiry;
use App\Models\Staff\User;
use Filament\Actions\CreateAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
});

test('can list catering inquiries in the table', function () {
    $inquiries = CateringInquiry::factory()->count(3)->create();

    Livewire::test(ListCateringInquiries::class)
        ->assertCanSeeTableRecords($inquiries);
});

test('can create a catering inquiry via slide-over', function () {
    Livewire::test(ListCateringInquiries::class)
        ->callAction(CreateAction::class, data: [
            'customer_name' => 'Jane Smith',
            'customer_email' => 'jane@example.com',
            'customer_phone' => '555-0100',
            'event_type' => 'Wedding',
            'event_date' => now()->addMonth()->format('Y-m-d'),
            'guest_count' => 50,
            'budget' => 2000,
            'details' => 'Wedding cake and pastries for 50 guests.',
            'status' => CateringInquiryStatus::Inquiry->value,
        ])
        ->assertHasNoFormErrors();

    test()->assertDatabaseHas(CateringInquiry::class, [
        'customer_name' => 'Jane Smith',
        'customer_email' => 'jane@example.com',
    ]);
});

test('create catering inquiry validates required fields', function (array $data, array $errors) {
    Livewire::test(ListCateringInquiries::class)
        ->callAction(CreateAction::class, data: [
            'customer_name' => 'Test',
            'customer_email' => 'test@example.com',
            'event_type' => 'Birthday Party',
            'event_date' => now()->addWeek()->format('Y-m-d'),
            'guest_count' => 20,
            'details' => 'Test details here.',
            'status' => CateringInquiryStatus::Inquiry->value,
            ...$data,
        ])
        ->assertHasFormErrors($errors);
})->with([
    'customer name is required' => [['customer_name' => null], ['customer_name' => 'required']],
    'customer email is required' => [['customer_email' => null], ['customer_email' => 'required']],
    'event type is required' => [['event_type' => null], ['event_type' => 'required']],
    'event date is required' => [['event_date' => null], ['event_date' => 'required']],
    'guest count is required' => [['guest_count' => null], ['guest_count' => 'required']],
    'details is required' => [['details' => null], ['details' => 'required']],
    'status is required' => [['status' => null], ['status' => 'required']],
]);

test('can render catering inquiry table columns', function (string $column) {
    CateringInquiry::factory()->create();

    Livewire::test(ListCateringInquiries::class)
        ->assertCanRenderTableColumn($column);
})->with(['customer_name', 'event_type', 'event_date', 'guest_count', 'status', 'quoted_amount']);

test('can search catering inquiries by customer name', function () {
    $target = CateringInquiry::factory()->create(['customer_name' => 'Alice Baker']);
    $other = CateringInquiry::factory()->create(['customer_name' => 'Bob Smith']);

    Livewire::test(ListCateringInquiries::class)
        ->searchTable('Alice')
        ->assertCanSeeTableRecords(collect([$target]))
        ->assertCanNotSeeTableRecords(collect([$other]));
});

test('can filter catering inquiries by status', function () {
    $inquiry = CateringInquiry::factory()->inquiry()->create();
    $confirmed = CateringInquiry::factory()->confirmed()->create();

    Livewire::test(ListCateringInquiries::class)
        ->filterTable('status', CateringInquiryStatus::Inquiry->value)
        ->assertCanSeeTableRecords(collect([$inquiry]))
        ->assertCanNotSeeTableRecords(collect([$confirmed]));
});

test('can filter catering inquiries by event type', function () {
    $wedding = CateringInquiry::factory()->create(['event_type' => 'Wedding']);
    $corporate = CateringInquiry::factory()->create(['event_type' => 'Corporate Event']);

    Livewire::test(ListCateringInquiries::class)
        ->filterTable('event_type', 'Wedding')
        ->assertCanSeeTableRecords(collect([$wedding]))
        ->assertCanNotSeeTableRecords(collect([$corporate]));
});

test('can sort catering inquiries by customer name', function () {
    $alice = CateringInquiry::factory()->create(['customer_name' => 'Alice']);
    $zach = CateringInquiry::factory()->create(['customer_name' => 'Zach']);

    Livewire::test(ListCateringInquiries::class)
        ->sortTable('customer_name')
        ->assertCanSeeTableRecords(collect([$alice, $zach]), inOrder: true)
        ->sortTable('customer_name', 'desc')
        ->assertCanSeeTableRecords(collect([$zach, $alice]), inOrder: true);
});

test('can sort catering inquiries by event date', function () {
    $early = CateringInquiry::factory()->create(['event_date' => now()->addWeek()]);
    $late = CateringInquiry::factory()->create(['event_date' => now()->addMonths(3)]);

    Livewire::test(ListCateringInquiries::class)
        ->sortTable('event_date')
        ->assertCanSeeTableRecords(collect([$early, $late]), inOrder: true)
        ->sortTable('event_date', 'desc')
        ->assertCanSeeTableRecords(collect([$late, $early]), inOrder: true);
});

test('resource returns globally searchable attributes', function () {
    expect(CateringInquiryResource::getGloballySearchableAttributes())
        ->toBe(['customer_name', 'customer_email']);
});

test('resource returns global search result title', function () {
    $inquiry = CateringInquiry::factory()->create(['customer_name' => 'Jane Baker']);

    expect(CateringInquiryResource::getGlobalSearchResultTitle($inquiry))
        ->toBe('Jane Baker');
});

test('resource returns global search result details', function () {
    $inquiry = CateringInquiry::factory()->create([
        'customer_email' => 'jane@example.com',
        'event_type' => 'Wedding',
    ]);

    $details = CateringInquiryResource::getGlobalSearchResultDetails($inquiry);

    expect($details)
        ->toHaveKey('Email', 'jane@example.com')
        ->toHaveKey('Event');
});
