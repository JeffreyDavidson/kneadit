<?php

namespace App\Filament\Central\Resources\TenantResource\Pages;

use App\Filament\Central\Resources\TenantResource;
use App\Models\Platform\AdminAuditLog;
use App\Models\Platform\Tenant;
use App\Models\Platform\TenantNote;
use App\Queries\Platform\TenantStatsQuery;
use App\Services\Tenants\TenantUrlGenerator;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\URL;
use Livewire\Attributes\Validate;

/**
 * @property-read Tenant $record
 */
class ViewTenant extends ViewRecord
{
    #[\Override]
    protected static string $resource = TenantResource::class;

    #[\Override]
    protected string $view = 'filament.central.pages.view-tenant';

    #[Validate(['required', 'min:3'])]
    public string $noteBody = '';

    #[\Override]
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
                ->url(fn (TenantUrlGenerator $urls): string => $urls->storefront($this->record))
                ->openUrlInNewTab(),
            Actions\EditAction::make(),
        ];
    }

    /** @return array<string, mixed> */
    public function getTenantStats(): array
    {
        try {
            return resolve(TenantStatsQuery::class)->forTenant($this->record);
        } catch (\Throwable) {
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
    #[\Override]
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
