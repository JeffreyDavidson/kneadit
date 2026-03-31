<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\CustomerPhoto;
use App\Services\Settings\TenantSettings;
use Illuminate\Contracts\View\View;

class ShowCateringController extends Controller
{
    public function __invoke(TenantSettings $settings): View
    {
        $cateringPhotos = CustomerPhoto::query()
            ->approved()
            ->withCaptionLike('catering')
            ->latest()
            ->take(8)
            ->get();

        $content = settingsPageContent('catering');

        return view('catering', [
            'settings' => $settings,
            'cateringPhotos' => $cateringPhotos,
            'content' => $content,
            'occasions' => $content['occasions'] ?? config('kneadit.default_catering_occasions'),
            'processSteps' => $content['process_steps'] ?? config('kneadit.default_catering_process_steps'),
        ]);
    }
}
