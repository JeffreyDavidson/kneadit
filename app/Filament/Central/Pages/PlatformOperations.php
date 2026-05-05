<?php

namespace App\Filament\Central\Pages;

use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Artisan;
use Throwable;
use UnitEnum;

class PlatformOperations extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBolt;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Operations';

    protected static ?string $title = 'Platform Operations';

    protected static ?int $navigationSort = 50;

    protected string $view = 'filament.central.pages.platform-operations';

    /**
     * Catalog of cron-style commands that admins occasionally need to fire on
     * demand without SSH'ing to the server. Each entry is rendered as a card
     * with a "Run Now" button that synchronously invokes the artisan command.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getCommands(): array
    {
        return [
            [
                'key' => 'health:check',
                'label' => 'Health Check',
                'description' => 'Run platform health checks (database, disk space, homepage, storage logs) and alert on failures.',
                'icon' => Heroicon::OutlinedHeart,
                'color' => 'emerald',
            ],
            [
                'key' => 'trial:check',
                'label' => 'Trial Expirations',
                'description' => 'Send reminder emails for trials ending soon and pause storefronts whose trials have ended.',
                'icon' => Heroicon::OutlinedClock,
                'color' => 'amber',
            ],
            [
                'key' => 'churn:check',
                'label' => 'Churn Alerts',
                'description' => 'Re-evaluate churn risk indicators and log alerts to the audit log.',
                'icon' => Heroicon::OutlinedExclamationTriangle,
                'color' => 'red',
            ],
            [
                'key' => 'checkins:send',
                'label' => 'Scheduled Check-ins',
                'description' => 'Send scheduled check-in emails to tenants based on their signup date.',
                'icon' => Heroicon::OutlinedEnvelope,
                'color' => 'sky',
            ],
            [
                'key' => 'digest:weekly',
                'label' => 'Weekly Digest',
                'description' => 'Send weekly digest email to all bakery owners.',
                'icon' => Heroicon::OutlinedNewspaper,
                'color' => 'honey',
            ],
            [
                'key' => 'platform:audit-free-forever',
                'label' => 'Audit Free Forever',
                'description' => 'Alert when a tenant is marked free_forever without an approved grant row.',
                'icon' => Heroicon::OutlinedGift,
                'color' => 'gold',
            ],
            [
                'key' => 'paypal:check-payments',
                'label' => 'PayPal Payment Sync',
                'description' => 'Reconcile PayPal invoice statuses across all tenants. Updates unpaid/cancelled/refunded orders.',
                'icon' => Heroicon::OutlinedCreditCard,
                'color' => 'sky',
            ],
            [
                'key' => 'campaigns:send-scheduled',
                'label' => 'Send Scheduled Campaigns',
                'description' => 'Process customer campaigns whose scheduled_at has arrived across all tenants.',
                'icon' => Heroicon::OutlinedPaperAirplane,
                'color' => 'honey',
            ],
        ];
    }

    public function getLastRun(string $key): ?string
    {
        $value = platformSettings('last_run_' . $key);

        return $value ?: null;
    }

    public function run(string $key): void
    {
        $known = collect($this->getCommands())->pluck('key')->all();

        if (! in_array($key, $known, true)) {
            Notification::make()->title('Unknown command')->danger()->send();

            return;
        }

        try {
            $exit = Artisan::call($key);
            $output = trim(Artisan::output());

            platformSettings(['last_run_' . $key => now()->toIso8601String()]);

            if ($exit === 0) {
                Notification::make()
                    ->title("Ran {$key}")
                    ->body($output !== '' ? $output : 'Completed.')
                    ->success()
                    ->send();
            } else {
                Notification::make()
                    ->title("{$key} returned exit code {$exit}")
                    ->body($output !== '' ? $output : 'No output.')
                    ->danger()
                    ->send();
            }
        } catch (Throwable $e) {
            Notification::make()
                ->title("Failed to run {$key}")
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
