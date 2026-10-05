<?php

use App\Enums\Financial\ExpenseCategory;
use App\Filament\Resources\Expenses\Pages\ListExpenses;
use App\Models\Financial\Expense;
use App\Models\Staff\User;
use App\Services\Settings\TenantSettings;
use Filament\Actions\CreateAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;
use Laravel\Pennant\Feature;

use function Pest\Livewire\livewire;

pest()->use(RefreshDatabase::class);

beforeEach(function () {
    setUpTenantTest();
    test()->actingAs(User::factory()->owner()->create());
    Feature::define('growth-features', fn () => true);
});

test('can list expenses in the table', function () {
    $expenses = Expense::factory()->count(3)->create();

    livewire(ListExpenses::class)
        ->assertCanSeeTableRecords($expenses);
});

test('can create an expense via slide-over', function () {
    livewire(ListExpenses::class)
        ->callAction(CreateAction::class, data: [
            'description' => 'Flour delivery',
            'amount' => 150.00,
            'category' => ExpenseCategory::Ingredients->value,
            'date' => '2026-03-26',
            'business_percentage' => 100,
        ])
        ->assertHasNoFormErrors();

    test()->assertDatabaseHas(Expense::class, [
        'description' => 'Flour delivery',
    ]);
});

test('can render expense table columns', function () {
    Expense::factory()->create();

    livewire(ListExpenses::class)
        ->assertCanRenderTableColumn('date')
        ->assertCanRenderTableColumn('description')
        ->assertCanRenderTableColumn('category')
        ->assertCanRenderTableColumn('amount');
});

test('can edit an expense via table action', function () {
    $expense = Expense::factory()->create();

    livewire(ListExpenses::class)
        ->callAction(TestAction::make('edit')->table($expense), data: [
            'description' => 'Updated expense',
            'amount' => $expense->amount->dollars(),
            'category' => $expense->category->value,
            'date' => $expense->date->format('Y-m-d'),
            'business_percentage' => 100,
        ])
        ->assertHasNoFormErrors();

    expect($expense->fresh()->description)->toBe('Updated expense');
});

test('create expense validates required fields', function () {
    $cases = [
        [['description' => null], ['description' => 'required']],
        [['amount' => null], ['amount' => 'required']],
        [['category' => null], ['category' => 'required']],
        [['date' => null], ['date' => 'required']],
    ];

    foreach ($cases as [$data, $errors]) {
        livewire(ListExpenses::class)
            ->callAction(CreateAction::class, data: [
                'description' => 'Test',
                'amount' => 50,
                'category' => ExpenseCategory::Ingredients->value,
                'date' => '2026-03-26',
                'business_percentage' => 100,
                ...$data,
            ])
            ->assertHasFormErrors($errors);
    }
});

test('can filter expenses by category', function () {
    $ingredients = Expense::factory()->forCategory(ExpenseCategory::Ingredients)->create();
    $packaging = Expense::factory()->forCategory(ExpenseCategory::Packaging)->create();

    livewire(ListExpenses::class)
        ->filterTable('category', ExpenseCategory::Ingredients->value)
        ->assertCanSeeTableRecords(collect([$ingredients]))
        ->assertCanNotSeeTableRecords(collect([$packaging]));
});

test('can search expenses by description', function () {
    $target = Expense::factory()->create(['description' => 'Flour delivery']);
    $other = Expense::factory()->create(['description' => 'Oven repair']);

    livewire(ListExpenses::class)
        ->searchTable('Flour')
        ->assertCanSeeTableRecords(collect([$target]))
        ->assertCanNotSeeTableRecords(collect([$other]));
});

test('can sort expenses by amount', function () {
    $small = Expense::factory()->create(['amount' => 10]);
    $large = Expense::factory()->create(['amount' => 500]);

    livewire(ListExpenses::class)
        ->sortTable('amount')
        ->assertCanSeeTableRecords(collect([$small, $large]), inOrder: true)
        ->sortTable('amount', 'desc')
        ->assertCanSeeTableRecords(collect([$large, $small]), inOrder: true);
});

test('amount range filter treats input as dollars (not cents)', function () {
    $belowRange = Expense::factory()->create(['amount' => 50]);
    $inRange = Expense::factory()->create(['amount' => 150]);
    $aboveRange = Expense::factory()->create(['amount' => 250]);

    livewire(ListExpenses::class)
        ->filterTable('amount', ['min_amount' => 100, 'max_amount' => 200])
        ->assertCanSeeTableRecords(collect([$inRange]))
        ->assertCanNotSeeTableRecords(collect([$belowRange, $aboveRange]));
});

test('new expenses default to the bakery-local date in the evening', function () {
    app()->instance(TenantSettings::class, makeTenantSettings(orders: makeOrderSettings(['timezone' => 'America/New_York'])));
    Date::setTestNow('2026-10-06 01:00');

    livewire(ListExpenses::class)
        ->mountAction(CreateAction::class)
        ->assertSchemaStateSet(['date' => '2026-10-05']);
});

test('a new receipt is stored on the private receipts disk, not the public one', function () {
    Storage::fake('receipts');
    Storage::fake('public');

    livewire(ListExpenses::class)
        ->callAction(CreateAction::class, data: [
            'description' => 'Flour delivery',
            'amount' => 150.00,
            'category' => ExpenseCategory::Ingredients->value,
            'date' => '2026-03-26',
            'business_percentage' => 100,
            'receipt_image' => UploadedFile::fake()->image('receipt.jpg'),
        ])
        ->assertHasNoFormErrors();

    $receipt = Expense::query()->firstOrFail()->receipt_image;

    expect($receipt)->toBeString();
    Storage::disk('receipts')->assertExists($receipt);
    expect(Storage::disk('public')->allFiles())->toBeEmpty();
});

test('the receipt column links to the authorised receipt route', function () {
    $expense = Expense::factory()->create(['receipt_image' => 'receipt.jpg']);
    $without = Expense::factory()->create(['receipt_image' => null]);

    livewire(ListExpenses::class)
        ->assertTableColumnStateSet('receipt_image', route('admin.expenses.receipt', $expense), record: $expense)
        ->assertTableColumnStateSet('receipt_image', null, record: $without);
});

test('the edit form previews a stored receipt through the authorised receipt route', function () {
    $expense = Expense::factory()->create(['receipt_image' => 'receipts/legacy.jpg']);

    $files = livewire(ListExpenses::class)
        ->mountAction(TestAction::make('edit')->table($expense))
        ->call('callSchemaComponentMethod', 'mountedActionSchema0.receipt_image', 'getUploadedFiles')
        ->effects['returns'][0];

    expect(array_values($files))->toBe([[
        'name' => 'legacy.jpg',
        'size' => 0,
        'type' => 'image/jpeg',
        'url' => route('admin.expenses.receipt', $expense),
    ]]);
});

test('editing an expense keeps a receipt saved on the public disk', function () {
    Storage::fake('receipts');
    Storage::fake('public');
    Storage::disk('public')->put('receipts/legacy.jpg', 'image bytes');
    $expense = Expense::factory()->create(['receipt_image' => 'receipts/legacy.jpg']);

    livewire(ListExpenses::class)
        ->callAction(TestAction::make('edit')->table($expense), data: [
            'description' => 'Updated expense',
            'amount' => $expense->amount->dollars(),
            'category' => $expense->category->value,
            'date' => $expense->date->format('Y-m-d'),
            'business_percentage' => 100,
        ])
        ->assertHasNoFormErrors();

    expect($expense->fresh()->receipt_image)->toBe('receipts/legacy.jpg');
});
