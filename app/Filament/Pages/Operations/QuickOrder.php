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
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

/**
 * @property-read Schema $form
 */
class QuickOrder extends Page
{
    use RequiresManagerRole;

    protected string $view = 'filament-panels::pages.page';

    /**
     * Hidden from the sidebar — Quick Order is now reached only via the
     * Quick Actions widget on the dashboard ("New Order" button) and the
     * colourful QuickActionsWidget icon grid. The route stays registered
     * so those buttons keep working.
     */
    protected static bool $shouldRegisterNavigation = false;

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
