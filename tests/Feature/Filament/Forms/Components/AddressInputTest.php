<?php

use App\Enums\Orders\PaymentMethod;
use App\Filament\Forms\Components\AddressInput;
use App\Filament\Pages\Settings\ManageSettings;
use App\Filament\Resources\CateringInquiries\Pages\ViewCateringInquiry;
use App\Filament\Resources\Customers\Pages\ListCustomers;
use App\Models\Customers\CateringInquiry;
use App\Models\Customers\Customer;
use App\Models\Staff\User;
use Filament\Actions\CreateAction;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
});

test('renders the address suggestions component limited to the bakery country when a Google key is set', function (string $storePhone, string $country) {
    config(['services.google_maps.browser_key' => 'test-browser-key']);
    settings(['store_phone' => $storePhone]);

    livewire(ManageSettings::class)
        ->assertFormFieldExists('store_address', fn (AddressInput $field): bool => $field->getCountry() === $country
            && $field->getPartStatePaths() === ['city' => 'store_city', 'state' => 'store_state', 'zip' => 'store_zip'])
        ->assertSeeHtml('x-data="addressInput({')
        ->assertSeeHtml("key: 'test-browser-key'")
        ->assertSeeHtml("country: '{$country}'")
        ->assertSeeHtml('data-address-input');
})->with([
    'US bakery' => ['+19133877359', 'us'],
    'UK bakery' => ['+442079460958', 'gb'],
    'no store phone yet' => ['', 'us'],
]);

test('renders a plain text box without Google when no key is set', function () {
    config(['services.google_maps.browser_key' => null]);

    livewire(ManageSettings::class)
        ->assertFormFieldExists('store_address')
        ->assertDontSeeHtml('addressInput(')
        ->assertDontSeeHtml('data-address-input')
        ->assertSeeHtml('wire:model="store_address"');
});

test('Manage Settings still saves a typed store address, city, state and ZIP', function (?string $key) {
    config(['services.google_maps.browser_key' => $key]);
    settings([
        'store_name' => 'Test Bakery',
        'payment_methods' => json_encode([PaymentMethod::Cash->value]),
    ]);

    livewire(ManageSettings::class)
        ->set('store_address', '123 Baker St')
        ->set('store_city', 'Tampa')
        ->set('store_state', 'FL')
        ->set('store_zip', '33601')
        ->call('save')
        ->assertHasNoErrors();

    expect(settings('store_address'))->toBe('123 Baker St')
        ->and(settings('store_city'))->toBe('Tampa')
        ->and(settings('store_state'))->toBe('FL')
        ->and(settings('store_zip'))->toBe('33601');
})->with([
    'with a Google key' => ['test-browser-key'],
    'without a Google key' => [null],
]);

test('the customer form fills its own address parts from a suggestion and saves a typed address', function () {
    config(['services.google_maps.browser_key' => 'test-browser-key']);

    livewire(ListCustomers::class)
        ->mountAction(CreateAction::class)
        ->assertFormFieldExists('address', fn (AddressInput $field): bool => $field->getPartStatePaths() === [
            'city' => 'mountedActions.0.data.city',
            'state' => 'mountedActions.0.data.state',
            'zip' => 'mountedActions.0.data.zip',
        ])
        ->fillForm([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'address' => '45 Garden Lane',
            'city' => 'Tampa',
            'state' => 'FL',
            'zip' => '33601',
        ])
        ->callMountedAction()
        ->assertHasNoFormErrors();

    assertDatabaseHas(Customer::class, [
        'email' => 'jane@example.com',
        'address' => '45 Garden Lane',
        'city' => 'Tampa',
        'state' => 'FL',
        'zip' => '33601',
    ]);
});

test('the catering venue address takes the whole suggested address and saves a typed one', function () {
    config(['services.google_maps.browser_key' => 'test-browser-key']);
    $inquiry = CateringInquiry::factory()->create();

    livewire(ViewCateringInquiry::class, ['record' => $inquiry->getRouteKey()])
        ->mountAction('editEventDetails')
        ->assertFormFieldExists('venue_address', fn (AddressInput $field): bool => $field->getPartStatePaths() === [] && $field->getRows() === 2)
        ->fillForm(['venue_address' => '45 Garden Lane, Tampa, FL 33601'])
        ->callMountedAction()
        ->assertHasNoFormErrors();

    expect($inquiry->fresh()->venue_address)->toBe('45 Garden Lane, Tampa, FL 33601');
});

test('the customer address keeps its length limit', function () {
    livewire(ListCustomers::class)
        ->callAction(CreateAction::class, data: [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'address' => str_repeat('a', 256),
        ])
        ->assertHasFormErrors(['address' => 'max']);
});
