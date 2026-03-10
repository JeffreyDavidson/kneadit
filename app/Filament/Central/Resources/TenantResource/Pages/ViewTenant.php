<?php

namespace App\Filament\Central\Resources\TenantResource\Pages;

use App\Filament\Central\Resources\TenantResource;
use App\Models\Tenant;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\URL;

class ViewTenant extends ViewRecord
{
    protected static string $resource = TenantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('impersonate')
                ->label('Login as Baker')
                ->icon('heroicon-o-finger-print')
                ->color('warning')
                ->url(fn (Tenant $record) => URL::signedRoute('tenant.impersonate', ['tenant' => $record->id]))
                ->openUrlInNewTab(),
            Actions\Action::make('visit')
                ->label('Visit Storefront')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('info')
                ->url(fn (Tenant $record) => 'https://' . $record->id . '.getkneadit.app')
                ->openUrlInNewTab(),
            Actions\EditAction::make(),
        ];
    }
}
