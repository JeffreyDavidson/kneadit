<?php

use App\Enums\Marketing\BulkMessagePurpose;
use App\Filament\Resources\Customers\CustomerResource;
use App\Filament\Resources\Customers\Pages\ListCustomers;
use App\Mail\Concerns\MarketingMail;
use App\Mail\Customers\BulkCustomerMessageMail;
use App\Mail\Customers\OrderUpdateMessageMail;
use App\Models\Customers\Customer;
use App\Models\Orders\Order;
use App\Models\Staff\User;
use App\Services\Settings\TenantSettings;
use Filament\Actions\CreateAction;
use Filament\Actions\Testing\TestAction;
use Filament\Notifications\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Mail;

use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
});

test('can render customers list page', function () {
    livewire(ListCustomers::class)
        ->assertOk();
});

test('can list customers in the table', function () {
    $customers = Customer::factory()->count(3)->create();

    livewire(ListCustomers::class)
        ->assertCanSeeTableRecords($customers);
});

test('can create a customer via slide-over', function () {
    livewire(ListCustomers::class)
        ->callAction(CreateAction::class, data: [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '+19133877359',
        ])
        ->assertHasNoFormErrors();

    test()->assertDatabaseHas(Customer::class, [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
    ]);
});

test('create customer rejects an email that differs only by case from an existing customer', function () {
    Customer::factory()->create(['email' => 'jane@example.com']);

    livewire(ListCustomers::class)
        ->callAction(CreateAction::class, data: [
            'name' => 'Jane Doe',
            'email' => 'Jane@Example.com',
        ])
        ->assertHasFormErrors(['email' => 'unique']);

    expect(Customer::query()->count())->toBe(1);
});

test('create customer validates required fields', function () {
    $cases = [
        [['name' => null, 'email' => 'test@test.com'], ['name' => 'required']],
        [['name' => 'Test', 'email' => null], ['email' => 'required']],
        [['name' => 'Test', 'email' => 'not-email'], ['email' => 'email']],
    ];

    foreach ($cases as [$data, $errors]) {
        livewire(ListCustomers::class)
            ->callAction(CreateAction::class, data: $data)
            ->assertHasFormErrors($errors);
    }
});

test('can edit a customer via table action', function () {
    $customer = Customer::factory()->create();

    livewire(ListCustomers::class)
        ->callAction(TestAction::make('edit')->table($customer), data: [
            'name' => 'Updated Name',
            'email' => $customer->email,
        ])
        ->assertHasNoFormErrors();

    expect($customer->fresh()->name)->toBe('Updated Name');
});

test('can search customers by name', function () {
    $alice = Customer::factory()->create(['name' => 'Alice Baker']);
    $bob = Customer::factory()->create(['name' => 'Bob Smith']);

    livewire(ListCustomers::class)
        ->searchTable('Alice')
        ->assertCanSeeTableRecords(collect([$alice]))
        ->assertCanNotSeeTableRecords(collect([$bob]));
});

test('can render customer table columns', function () {
    Customer::factory()->create();

    livewire(ListCustomers::class)
        ->assertCanRenderTableColumn('name')
        ->assertCanRenderTableColumn('email')
        ->assertCanRenderTableColumn('phone')
        ->assertCanRenderTableColumn('orders_count');
});

test('can sort customers by name', function () {
    $alice = Customer::factory()->create(['name' => 'Alice']);
    $zach = Customer::factory()->create(['name' => 'Zach']);

    livewire(ListCustomers::class)
        ->sortTable('name')
        ->assertCanSeeTableRecords(collect([$alice, $zach]), inOrder: true)
        ->sortTable('name', 'desc')
        ->assertCanSeeTableRecords(collect([$zach, $alice]), inOrder: true);
});

test('can filter customers with birthday this month', function () {
    $birthday = Customer::factory()->create(['birthday' => now()->format('Y-m-d')]);
    $noBirthday = Customer::factory()->create(['birthday' => null]);

    livewire(ListCustomers::class)
        ->filterTable('has_birthday_this_month')
        ->assertCanSeeTableRecords(collect([$birthday]))
        ->assertCanNotSeeTableRecords(collect([$noBirthday]));
});

test('resource returns globally searchable attributes', function () {
    expect(CustomerResource::getGloballySearchableAttributes())
        ->toBe(['name', 'email', 'phone']);
});

test('resource returns global search result title', function () {
    $customer = Customer::factory()->create(['name' => 'Alice Baker']);

    expect(CustomerResource::getGlobalSearchResultTitle($customer))
        ->toBe('Alice Baker');
});

test('resource returns global search result details', function () {
    $customer = Customer::factory()->create([
        'email' => 'alice@example.com',
        'phone' => '5550100',
    ]);

    $details = CustomerResource::getGlobalSearchResultDetails($customer);

    expect($details)
        ->toHaveKey('Email', 'alice@example.com')
        ->toHaveKey('Phone');
});

test('owner can bulk-delete selected customers via the AuthorizedDeleteBulkAction', function () {
    $kept = Customer::factory()->create();
    $doomed = Customer::factory()->count(2)->create();

    livewire(ListCustomers::class)
        ->selectTableRecords($doomed)
        ->callAction(TestAction::make('delete')->table()->bulk());

    expect(Customer::query()->count())->toBe(1)
        ->and(Customer::query()->find($kept->id))->not->toBeNull()
        ->and(Customer::query()->find($doomed->first()->id))->toBeNull();
});

test('bulk delete keeps customers who have orders, and their orders', function () {
    $withOrder = Customer::factory()->create();
    $order = Order::factory()->for($withOrder)->create();
    $withoutOrder = Customer::factory()->create();

    livewire(ListCustomers::class)
        ->selectTableRecords([$withOrder, $withoutOrder])
        ->callAction(TestAction::make('delete')->table()->bulk());

    expect(Customer::query()->find($withOrder->id))->not->toBeNull()
        ->and(Order::query()->find($order->id))->not->toBeNull()
        ->and(Customer::query()->find($withoutOrder->id))->toBeNull();
});

test('bulk delete names the customer it skipped and says to anonymise them instead', function () {
    $withOrder = Customer::factory()->create(['name' => 'Alice Baker']);
    Order::factory()->for($withOrder)->create();

    livewire(ListCustomers::class)
        ->selectTableRecords([$withOrder])
        ->callAction(TestAction::make('delete')->table()->bulk())
        ->assertNotified(Notification::make()
            ->danger()
            ->persistent()
            ->title('Failed to delete')
            ->body("<p>Alice Baker has orders, so they can't be deleted. Anonymise them instead to remove their personal details and keep the order history.</p>"));
});

test('bulk delete explains why a customer was skipped when others are deleted', function () {
    $withOrder = Customer::factory()->create(['name' => 'Alice Baker']);
    Order::factory()->for($withOrder)->create();
    $withoutOrder = Customer::factory()->create();

    livewire(ListCustomers::class)
        ->selectTableRecords([$withOrder, $withoutOrder])
        ->callAction(TestAction::make('delete')->table()->bulk())
        ->assertNotified('Deleted 1 of 2');

    expect(Customer::query()->find($withoutOrder->id))->toBeNull();
});

test('email marketing column shows subscribed and unsubscribed customers', function () {
    Date::setTestNow('2026-10-06 12:00');
    $subscribed = Customer::factory()->create();
    $unsubscribed = Customer::factory()->unsubscribed()->create(['marketing_opted_out_at' => '2026-09-20 09:00']);

    livewire(ListCustomers::class)
        ->assertTableColumnExists('email_marketing')
        ->assertTableColumnFormattedStateSet('email_marketing', 'Subscribed', $subscribed)
        ->assertTableColumnFormattedStateSet('email_marketing', 'Unsubscribed since Sep 20, 2026', $unsubscribed);
});

test('email marketing filter narrows the table to subscribed or unsubscribed customers', function (string $value, bool $expectsSubscribed) {
    $subscribed = Customer::factory()->create();
    $unsubscribed = Customer::factory()->unsubscribed()->create();

    livewire(ListCustomers::class)
        ->filterTable('email_marketing', $value)
        ->assertCanSeeTableRecords($expectsSubscribed ? [$subscribed] : [$unsubscribed])
        ->assertCanNotSeeTableRecords($expectsSubscribed ? [$unsubscribed] : [$subscribed]);
})->with([
    'subscribed' => ['subscribed', true],
    'unsubscribed' => ['unsubscribed', false],
]);

test('staff can mark a customer unsubscribed but cannot re-subscribe them', function () {
    Date::setTestNow('2026-10-06 12:00');
    $customer = Customer::factory()->create();
    $alreadyOptedOut = Customer::factory()->unsubscribed()->create();

    livewire(ListCustomers::class)
        ->assertActionHidden(TestAction::make('markUnsubscribed')->table($alreadyOptedOut))
        ->callAction(TestAction::make('markUnsubscribed')->table($customer));

    expect($customer->fresh()->marketing_opted_out_at?->toDateTimeString())->toBe('2026-10-06 12:00:00')
        ->and($alreadyOptedOut->fresh()->marketing_opted_out_at)->not->toBeNull();
    livewire(ListCustomers::class)
        ->assertActionDoesNotExist(TestAction::make('resubscribe')->table($alreadyOptedOut));
});

test('customer birthday cannot be after the bakery-local today', function () {
    app()->instance(TenantSettings::class, makeTenantSettings(orders: makeOrderSettings(['timezone' => 'America/New_York'])));
    Date::setTestNow('2026-10-06 01:00');

    livewire(ListCustomers::class)
        ->callAction(CreateAction::class, data: [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'birthday' => '2026-10-06',
        ])
        ->assertHasFormErrors(['birthday']);
});

dataset('bulk message purposes and customers', [
    'order update, opted out with an open order' => [
        BulkMessagePurpose::OrderUpdate,
        fn (): Customer => Customer::factory()->unsubscribed()->has(Order::factory()->confirmed())->create(),
        OrderUpdateMessageMail::class,
        '1 sent',
    ],
    'order update, subscribed with an open order' => [
        BulkMessagePurpose::OrderUpdate,
        fn (): Customer => Customer::factory()->has(Order::factory()->ready())->create(),
        OrderUpdateMessageMail::class,
        '1 sent',
    ],
    'order update, no open order' => [
        BulkMessagePurpose::OrderUpdate,
        fn (): Customer => Customer::factory()->has(Order::factory()->delivered())->create(),
        null,
        '0 sent; 1 skipped (no open order)',
    ],
    'promotion, opted out' => [
        BulkMessagePurpose::Promotion,
        fn (): Customer => Customer::factory()->unsubscribed()->has(Order::factory()->confirmed())->create(),
        null,
        '0 sent; 1 skipped (unsubscribed)',
    ],
    'promotion, subscribed' => [
        BulkMessagePurpose::Promotion,
        fn (): Customer => Customer::factory()->create(),
        BulkCustomerMessageMail::class,
        '1 sent',
    ],
]);

test('bulk send message honours the chosen purpose', function (BulkMessagePurpose $purpose, Closure $makeCustomer, ?string $expectedMail, string $expectedTitle) {
    Mail::fake();
    $customer = $makeCustomer();

    livewire(ListCustomers::class)
        ->selectTableRecords([$customer])
        ->callAction(TestAction::make('sendMessage')->table()->bulk(), data: [
            'purpose' => $purpose,
            'subject' => 'Pickup window changed',
            'body' => 'Pickup is now 3pm to 5pm.',
        ])
        ->assertNotified(Notification::make()->success()->title($expectedTitle));

    if ($expectedMail === null) {
        Mail::assertNothingQueued();

        return;
    }

    Mail::assertQueued($expectedMail, 1);
    Mail::assertQueued($expectedMail, fn (Mailable $mail): bool => $mail->hasTo($customer->email)
        && $mail instanceof MarketingMail === ($purpose === BulkMessagePurpose::Promotion));
})->with('bulk message purposes and customers');

test('bulk send message requires a purpose', function () {
    Mail::fake();
    $customer = Customer::factory()->create();

    livewire(ListCustomers::class)
        ->selectTableRecords([$customer])
        ->callAction(TestAction::make('sendMessage')->table()->bulk(), data: [
            'subject' => 'Hello',
            'body' => 'A note.',
        ])
        ->assertHasFormErrors(['purpose' => 'required']);

    Mail::assertNothingQueued();
});
