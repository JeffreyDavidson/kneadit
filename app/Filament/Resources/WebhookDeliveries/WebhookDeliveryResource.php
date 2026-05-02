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

    protected static ?string $model = WebhookDelivery::class;

    protected static ?string $recordTitleAttribute = 'event';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBoltSlash;

    protected static string|\UnitEnum|null $navigationGroup = 'Operations';

    protected static ?string $navigationLabel = 'Webhook Deliveries';

    protected static ?int $navigationSort = 95;

    public static function canAccess(): bool
    {
        return static::hasManagerAccess();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return WebhookDeliveriesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWebhookDeliveries::route('/'),
        ];
    }
}
