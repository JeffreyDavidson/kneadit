<?php

namespace App\Mail;

use App\Services\Settings\TenantSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\Attributes\Backoff;
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Queue\SerializesModels;

#[Tries(3)]
#[Backoff([10, 60, 300])]
abstract class BaseMailable extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @return array<string, mixed>
     */
    public function buildViewData(): array
    {
        $settings = app(TenantSettings::class);

        return array_merge(parent::buildViewData(), [
            'storeName' => $settings->storeName,
            'primaryColor' => $settings->brandColorPrimary,
            'secondaryColor' => tenant()->brand_color_secondary ?? '#1c1410',
            'storeEmail' => $settings->storeEmail ?? '',
            'storePhone' => $settings->storePhone ?? '',
            'storeAddress' => $settings->storeAddress ?? '',
            'logoUrl' => $settings->storeLogoUrl(),
        ]);
    }
}
