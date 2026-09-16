<?php

namespace App\Filament\Resources\WebhookDeliveries;

use App\Filament\Concerns\RequiresManagerRole;
use App\Filament\Resources\WebhookDeliveries\Pages\ListWebhookDeliveries;
use App\Filament\Resources\WebhookDeliveries\Tables\WebhookDeliveriesTable;
use App\Models\Operations\WebhookDelivery;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * Read-only viewer for outbound webhook delivery attempts. Every call to
 * WebhookService::dispatch() writes a row here; the baker uses this page
 * to confirm their endpoint is reachable and to debug failed deliveries.
 *
 * Mutations are deliberately disabled — the audit trail is not editable.
 * The Redeliver action creates a NEW delivery row via the service.
 */
class WebhookDeliveryResource extends Resource
{
    use RequiresManagerRole;

    #[\Override]
    protected static ?string $model = WebhookDelivery::class;

    #[\Override]
    protected static ?string $recordTitleAttribute = 'event';

    #[\Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBoltSlash;

    #[\Override]
    protected static string|\UnitEnum|null $navigationGroup = 'Operations';

    #[\Override]
    protected static ?string $navigationLabel = 'Webhook Deliveries';

    #[\Override]
    protected static ?int $navigationSort = 95;

    #[\Override]
    public static function canAccess(): bool
    {
        return static::hasManagerAccess();
    }

    #[\Override]
    public static function canCreate(): bool
    {
        return false;
    }

    #[\Override]
    public static function canEdit(Model $record): bool
    {
        return false;
    }

    #[\Override]
    public static function canDelete(Model $record): bool
    {
        return false;
    }

    #[\Override]
    public static function table(Table $table): Table
    {
        return WebhookDeliveriesTable::configure($table);
    }

    #[\Override]
    public static function getPages(): array
    {
        return [
            'index' => ListWebhookDeliveries::route('/'),
        ];
    }
}
