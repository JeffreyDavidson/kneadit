<?php

namespace App\Mail;

use App\Services\Settings\TenantSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

abstract class BaseMailable extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [10, 60, 300];

    /**
     * @return array<string, mixed>
     */
    public function buildViewData(): array
    {
        $settings = app(TenantSettings::class);

        return array_merge(parent::buildViewData(), [
            'storeName' => $settings->storeName,
            'primaryColor' => $settings->brandColorPrimary,
            'secondaryColor' => tenant()?->brand_color_secondary ?? '#1c1410',
            'storeEmail' => $settings->storeEmail ?? '',
            'storePhone' => $settings->storePhone ?? '',
            'storeAddress' => $settings->storeAddress ?? '',
            'logoUrl' => $settings->storeLogoUrl(),
        ]);
    }
}
