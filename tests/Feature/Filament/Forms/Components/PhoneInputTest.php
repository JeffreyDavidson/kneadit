<?php

use App\Enums\Orders\PaymentMethod;
use App\Filament\Forms\Components\PhoneInput;
use App\Filament\Pages\Settings\ManageSettings;
use App\Filament\Resources\Customers\Pages\ListCustomers;
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

test('renders the shared phone input with the country selector starting on the bakery country', function (string $storePhone, string $country) {
    settings(['store_phone' => $storePhone]);

    livewire(ManageSettings::class)
        ->assertFormFieldExists('store_phone', fn (PhoneInput $field): bool => $field->getDefaultCountry() === $country)
        ->assertSeeHtml('x-data="phoneInput({')
        ->assertSeeHtml("country: '{$country}'")
        ->assertSeeHtml('data-phone-input');
})->with([
    'US bakery' => ['+19133877359', 'us'],
    'UK bakery' => ['+442079460958', 'gb'],
    'no store phone yet' => ['', 'us'],
]);

test('saves the number in E.164', function (string $typed, string $stored) {
    livewire(ListCustomers::class)
        ->callAction(CreateAction::class, data: [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => $typed,
        ])
        ->assertHasNoFormErrors();

    assertDatabaseHas(Customer::class, ['email' => 'jane@example.com', 'phone' => $stored]);
})->with([
    'US from the input' => ['+19133877359', '+19133877359'],
    'UK from the input' => ['+442079460958', '+442079460958'],
    'US typed without a country code' => ['(913) 387-7359', '+19133877359'],
]);

test('rejects a number that is not complete for its country', function (string $typed) {
    livewire(ListCustomers::class)
        ->callAction(CreateAction::class, data: [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => $typed,
        ])
        ->assertHasFormErrors(['phone']);
})->with([
    'local seven digits' => ['555-0100'],
    'US missing a digit' => ['+1913387735'],
]);

test('the store phone in Manage Settings is saved in E.164', function () {
    settings([
        'store_name' => 'Test Bakery',
        'payment_methods' => json_encode([PaymentMethod::Cash->value]),
    ]);

    livewire(ManageSettings::class)
        ->set('store_phone', '(913) 387-7359')
        ->call('save')
        ->assertHasNoErrors();

    expect(settings('store_phone'))->toBe('+19133877359');
});
