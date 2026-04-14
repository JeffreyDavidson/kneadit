<?php

namespace App\Filament\Pages\Operations;

use App\Actions\Orders\CreateQuickOrder;
use App\DataTransferObjects\Orders\CreateQuickOrderData;
use App\Filament\Concerns\RequiresManagerRole;
use App\Filament\Pages\Operations\Schemas\QuickOrderForm;
use App\Models\Orders\Order;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class QuickOrder extends Page
{
    use RequiresManagerRole;

    protected string $view = 'filament-panels::pages.page';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPlusCircle;

    protected static string|\UnitEnum|null $navigationGroup = 'Shop';

    protected static ?string $title = 'Quick Order';

    protected static ?string $navigationLabel = 'Quick Order';

    protected static ?int $navigationSort = 7;

    /** @var array<string, mixed> */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return QuickOrderForm::configure($schema)
            ->statePath('data')
            ->model(Order::class);
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make(QuickOrderForm::getComponents())
                ->livewireSubmitHandler('submit'),
        ]);
    }

    public function createOrder(): void
    {
        $data = $this->form->getState();

        try {
            $order = resolve(CreateQuickOrder::class)(CreateQuickOrderData::fromArray($data));

            Notification::make()
                ->title('Order Created Successfully!')
                ->body("Order #{$order->order_number} has been created.")
                ->success()
                ->send();

            $this->form->fill();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error Creating Order')
                ->body('There was an error creating the order. Please try again.')
                ->danger()
                ->send();
        }
    }
}
