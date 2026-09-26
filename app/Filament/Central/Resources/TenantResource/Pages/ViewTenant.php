<?php

namespace App\Filament\Central\Resources\TenantResource\Pages;

use App\Actions\Platform\AddTenantNote;
use App\Actions\Platform\DeleteTenantNote;
use App\Filament\Central\Resources\TenantResource;
use App\Models\Platform\AdminAuditLog;
use App\Models\Platform\Tenant;
use App\Queries\Platform\TenantOverviewQuery;
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
    private TenantOverviewQuery $tenantOverviewQuery;

    #[\Override]
    protected static string $resource = TenantResource::class;

    #[\Override]
    protected string $view = 'filament.central.pages.view-tenant';

    #[Validate(['required', 'min:3'])]
    public string $noteBody = '';

    public function boot(TenantOverviewQuery $tenantOverviewQuery): void
    {
        $this->tenantOverviewQuery = $tenantOverviewQuery;
    }

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
            return $this->tenantOverviewQuery->adminStats($this->record);
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

    public function addNote(AddTenantNote $addTenantNote): void
    {
        $this->validate(['noteBody' => ['required', 'min:3']]);

        $addTenantNote(
            $this->record,
            $this->noteBody,
            auth()->user()->name ?? 'admin',
        );

        $this->noteBody = '';
        $this->record->load('notes');

        Notification::make()
            ->title('Note added')
            ->success()
            ->send();
    }

    public function deleteNote(int $noteId, DeleteTenantNote $deleteTenantNote): void
    {
        $deleteTenantNote($this->record, $noteId);

        $this->record->load('notes');

        Notification::make()
            ->title('Note deleted')
            ->success()
            ->send();
    }
}
