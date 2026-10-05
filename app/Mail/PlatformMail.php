<?php

declare(strict_types=1);

namespace App\Mail;

use App\DataTransferObjects\Settings\BrandingSettings;
use App\DataTransferObjects\Settings\SettingValue;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Config;

/**
 * A mail the platform sends from outside any bakery: platform admin alerts,
 * baker lifecycle and billing mail, and the marketing contact form.
 *
 * Bakery mail reads its branding from the current bakery's settings, which do
 * not exist in the central database, so a platform mail uses KneadIt's own
 * branding instead and never touches the tenant settings. Whatever the mail
 * passes itself (such as the `storeName` of the bakery it is about) wins over
 * these defaults.
 */
abstract class PlatformMail extends BaseMailable
{
    /**
     * @return array<string, mixed>
     */
    #[\Override]
    public function buildViewData(): array
    {
        return [
            'storeName' => 'KneadIt',
            'primaryColor' => BrandingSettings::DEFAULT_BRAND_COLOR,
            'secondaryColor' => '#1c1410',
            'storeEmail' => '',
            'storePhone' => '',
            'storeAddress' => '',
            'logoUrl' => null,
            'platformHomeUrl' => Config::string('app.url'),
            ...SettingValue::map(Mailable::buildViewData()),
        ];
    }
}
