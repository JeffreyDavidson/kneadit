<?php

declare(strict_types=1);

namespace App\Mail;

use App\DataTransferObjects\Settings\BrandingSettings;
use App\DataTransferObjects\Settings\SettingValue;
use App\Mail\Concerns\MarketingMail;
use App\Models\Platform\Tenant;
use App\Services\Settings\TenantSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\Attributes\Backoff;
use Illuminate\Queue\Attributes\Timeout;
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;

#[Tries(3)]
#[Backoff([10, 60, 300])]
#[Timeout(60)]
abstract class BaseMailable extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @return array<string, mixed>
     */
    #[\Override]
    public function buildViewData(): array
    {
        $settings = resolve(TenantSettings::class);
        $store = $settings->store;
        $tenant = tenancy()->tenant;
        $secondaryColor = $tenant instanceof Tenant ? $tenant->brand_color_secondary : null;

        $data = array_merge(SettingValue::map(parent::buildViewData()), [
            'storeName' => $store->name,
            'primaryColor' => $settings->branding->brandColorPrimary,
            'secondaryColor' => BrandingSettings::safeColor($secondaryColor, '#1c1410'),
            'storeEmail' => $store->email ?? '',
            'storePhone' => $store->phone ?? '',
            'storeAddress' => $store->address ?? '',
            'logoUrl' => $store->logoUrl(),
            'platformHomeUrl' => Config::string('app.url'),
        ]);

        if (! $this instanceof MarketingMail) {
            return $data;
        }

        return [...$data, 'unsubscribeUrl' => $this->unsubscribeUrl()];
    }
}
