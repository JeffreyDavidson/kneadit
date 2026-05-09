<?php

namespace App\Filament\Central\Resources\TenantResource\Pages;

use App\Filament\Central\Resources\TenantResource;
use App\Models\Platform\AdminAuditLog;
use App\Models\Platform\Tenant;
use App\Models\Platform\TenantNote;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Livewire\Attributes\Rule;

/**
 * @property-read Tenant $record
 */
class ViewTenant extends ViewRecord
{
    #[Rule(['required', 'min:3'])]
    public string $noteBody = '';

    protected static string $resource = TenantResource::class;

    protected string $view = 'filament.central.pages.view-tenant';

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('impersonate')
                ->label('Login as Baker')
                ->icon(Heroicon::OutlinedFingerPrint)
                ->color('warning')
                ->authorize('platform-admin')
                ->url(fn () => URL::signedRoute('tenant.impersonate', ['tenant' => $this->record->id]))
                ->openUrlInNewTab(),
            Actions\Action::make('visit')
                ->label('Visit Storefront')
                ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                ->color('info')
                ->url(fn () => sprintf(
                    '%s://%s.%s',
                    parse_url(config('app.url'), PHP_URL_SCHEME) ?: 'https',
                    $this->record->id,
                    parse_url(config('app.url'), PHP_URL_HOST) ?: 'getkneadit.app',
                ))
                ->openUrlInNewTab(),
            Actions\EditAction::make(),
        ];
    }

    /** @return array<string, mixed> */
    public function getTenantStats(): array
    {
        try {
            $this->record->run(function () use (&$stats) {
                $stats = [
                    'products' => DB::table('products')->count(),
                    'orders' => DB::table('orders')->count(),
                    // orders.total is bigint cents (migration 2026_04_22_201500).
                    'revenue' => (int) DB::table('orders')->sum('total') / 100,
                    'customers' => DB::table('users')->count(),
                    'reviews' => DB::table('reviews')->count(),
                    'last_order' => DB::table('orders')->max('created_at'),
                ];
            });

            return $stats ?? $this->emptyStats();
        } catch (\Throwable $e) {
            return $this->emptyStats();
        }
    }

    /** @return array<string, mixed> */
    private function emptyStats(): array
    {
        return [
            'products' => 0,
            'orders' => 0,
            'revenue' => 0,
            'customers' => 0,
            'reviews' => 0,
            'last_order' => null,
        ];
    }

    /**
     * Admin audit entries targeting this tenant. Used by the Activity tab.
     *
     * @return Collection<int, AdminAuditLog>
     */
    public function getTenantAuditEntries(int $limit = 25): Collection
    {
        return AdminAuditLog::query()
            ->where('target_type', Tenant::class)
            ->where('target_id', $this->record->id)
            ->latest()
            ->limit($limit)
            ->get();
    }

    /** Suppress auto-render of relation managers; we render Notes inside the Notes tab manually. */
    public function getAllRelationManagers(): array
    {
        return [];
    }

    public function addNote(): void
    {
        $this->validate(['noteBody' => ['required', 'min:3']]);

        $this->record->notes()->create([
            'body' => $this->noteBody,
            'author' => auth()->user()->name ?? 'admin',
        ]);

        $this->noteBody = '';
        $this->record->load('notes');

        Notification::make()
            ->title('Note added')
            ->success()
            ->send();
    }

    public function deleteNote(int $noteId): void
    {
        TenantNote::query()
            ->where('tenant_id', $this->record->id)
            ->where('id', $noteId)
            ->delete();

        $this->record->load('notes');

        Notification::make()
            ->title('Note deleted')
            ->success()
            ->send();
    }
}
