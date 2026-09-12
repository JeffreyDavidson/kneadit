<?php

namespace App\Mail;

use App\DataTransferObjects\Settings\SettingValue;
use App\Models\Platform\Tenant;
use App\Services\Settings\TenantSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\Attributes\Backoff;
use Illuminate\Queue\Attributes\Timeout;
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

#[Tries(3)]
#[Backoff([10, 60, 300])]
#[Timeout(60)]
abstract class BaseMailable extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @return array<string, mixed>
     */
    public function buildViewData(): array
    {
        $settings = resolve(TenantSettings::class);
        $store = $settings->store;
        $tenant = tenancy()->tenant;
        $secondaryColor = $tenant instanceof Tenant ? $tenant->brand_color_secondary : null;

        return array_merge(SettingValue::map(parent::buildViewData()), [
            'storeName' => $store->name,
            'primaryColor' => $settings->branding->brandColorPrimary,
            'secondaryColor' => $secondaryColor ?? '#1c1410',
            'storeEmail' => $store->email ?? '',
            'storePhone' => $store->phone ?? '',
            'storeAddress' => $store->address ?? '',
            'logoUrl' => $store->logoUrl(),
            'platformHomeUrl' => URL::route('home'),
        ]);
    }
}
