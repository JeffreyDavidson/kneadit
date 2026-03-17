<?php

namespace App\Filament\Central\Resources\TenantResource\Pages;

use App\Filament\Central\Resources\TenantResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

class ViewTenant extends ViewRecord
{
    protected static string $resource = TenantResource::class;

    protected string $view = 'filament.central.pages.view-tenant';

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('impersonate')
                ->label('Login as Baker')
                ->icon('heroicon-o-finger-print')
                ->color('warning')
                ->url(fn () => URL::signedRoute('tenant.impersonate', ['tenant' => $this->record->id]))
                ->openUrlInNewTab(),
            Actions\Action::make('visit')
                ->label('Visit Storefront')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('info')
                ->url(fn () => 'https://'.$this->record->id.'.getkneadit.app')
                ->openUrlInNewTab(),
            Actions\EditAction::make(),
        ];
    }

    public function getTenantStats(): array
    {
        try {
            $this->record->run(function () use (&$stats) {
                $stats = [
                    'products' => DB::table('products')->count(),
                    'orders' => DB::table('orders')->count(),
                    'revenue' => DB::table('orders')->sum('total') ?? 0,
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
