<?php

namespace App\Filament\Central\Resources\TenantResource\Pages;

use App\Filament\Central\Resources\TenantResource;
use App\Models\Platform\Tenant;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

/**
 * @property-read Tenant $record
 */
class ViewTenant extends ViewRecord
{
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
                ->url(fn () => 'https://' . $this->record->id . '.getkneadit.app')
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
}
