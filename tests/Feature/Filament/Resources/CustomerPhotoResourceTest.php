<?php

use App\Filament\Resources\CustomerPhotos\Pages\ListCustomerPhotos;
use App\Models\Customers\CustomerPhoto;
use App\Models\Staff\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Pennant\Feature;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
    Feature::define('pro-features', fn () => true);
});

test('can list customer photos in the table', function () {
    $photos = CustomerPhoto::factory()->count(3)->create();

    livewire(ListCustomerPhotos::class)
        ->assertCanSeeTableRecords($photos);
});

test('can render customer photo table columns', function () {
    CustomerPhoto::factory()->create();

    livewire(ListCustomerPhotos::class)
        ->assertCanRenderTableColumn('customer_name')
        ->assertCanRenderTableColumn('caption');
});

test('can create a customer photo via header action', function () {
    Storage::fake('public');

    livewire(ListCustomerPhotos::class)
        ->callAction('create', data: [
            'customer_name' => 'Alice Baker',
            'customer_email' => 'alice@example.com',
            'photo_path' => [UploadedFile::fake()->image('cake.jpg')],
            'caption' => 'My birthday cake!',
            'is_approved' => true,
            'is_featured' => false,
        ])
        ->assertHasNoFormErrors();

    expect(CustomerPhoto::query()->first())
        ->customer_name->toBe('Alice Baker')
        ->customer_email->toBe('alice@example.com')
        ->caption->toBe('My birthday cake!')
        ->photo_path->not->toBeNull();
});

test('create customer photo validates required fields', function () {
    $cases = [
        [['customer_name' => null], ['customer_name' => 'required']],
        [['customer_email' => null], ['customer_email' => 'required']],
        [['customer_email' => 'not-an-email'], ['customer_email' => 'email']],
    ];

    foreach ($cases as [$data, $errors]) {
        livewire(ListCustomerPhotos::class)
            ->callAction('create', data: [
                'customer_name' => 'Alice',
                'customer_email' => 'alice@example.com',
                ...$data,
            ])
            ->assertHasFormErrors($errors);
    }
});

test('can edit a customer photo via table action', function () {
    $photo = CustomerPhoto::factory()->create();

    livewire(ListCustomerPhotos::class)
        ->callAction(TestAction::make('edit')->table($photo), data: [
            'customer_name' => 'Updated Name',
            'customer_email' => $photo->customer_email,
            'photo_path' => [$photo->photo_path],
            'caption' => 'Updated caption',
            'is_approved' => true,
            'is_featured' => true,
        ])
        ->assertHasNoFormErrors();

    expect($photo->fresh())
        ->customer_name->toBe('Updated Name')
        ->caption->toBe('Updated caption')
        ->is_featured->toBeTrue();
});

test('can search customer photos by customer name', function () {
    $target = CustomerPhoto::factory()->create(['customer_name' => 'Alice Baker']);
    $other = CustomerPhoto::factory()->create(['customer_name' => 'Bob Smith']);

    livewire(ListCustomerPhotos::class)
        ->searchTable('Alice')
        ->assertCanSeeTableRecords(collect([$target]))
        ->assertCanNotSeeTableRecords(collect([$other]));
});
