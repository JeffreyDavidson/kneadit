<?php

namespace App\Filament\Pages\Operations;

use App\Filament\Concerns\RequiresManagerRole;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * Static documentation page for the webhook integration. Aimed at the
 * baker (or their developer) who needs to wire up an endpoint —
 * covers the event catalog, payload shapes, and signature verification.
 */
class WebhooksDocs extends Page
{
    use RequiresManagerRole;

    #[\Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    #[\Override]
    protected static string|UnitEnum|null $navigationGroup = 'Operations';

    #[\Override]
    protected static ?int $navigationSort = 96;

    #[\Override]
    protected static ?string $navigationLabel = 'Webhook Docs';

    #[\Override]
    protected static ?string $title = 'Webhook Documentation';

    #[\Override]
    protected string $view = 'filament.pages.operations.webhooks-docs';
}
