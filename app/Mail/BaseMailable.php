<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

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
        $storeName = settings('store_name', 'KneadIt Bakery');
        $logoPath = settings('store_logo');

        return array_merge(parent::buildViewData(), [
            'storeName' => $storeName,
            'primaryColor' => settings('brand_color_primary', '#d4920c'),
            'secondaryColor' => settings('brand_color_secondary', '#1c1410'),
            'storeEmail' => settings('store_email', ''),
            'storePhone' => settings('store_phone', ''),
            'storeAddress' => settings('store_address', ''),
            'logoUrl' => $logoPath ? Storage::url($logoPath) : null,
        ]);
    }
}
