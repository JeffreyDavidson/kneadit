<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Setting;

class AppIconController extends Controller
{
    /**
     * Generate a dynamic PWA app icon for the storefront.
     */
    public function __invoke($size)
    {
        $size = in_array($size, ['192', '512']) ? (int) $size : 192;
        $storeName = Setting::get('store_name', 'B');
        $color = tenant()->brand_color_primary ?? '#d4920c';

        $img = imagecreatetruecolor($size, $size);
        $r = hexdec(substr($color, 1, 2));
        $g = hexdec(substr($color, 3, 2));
        $b = hexdec(substr($color, 5, 2));
        $bgColor = imagecolorallocate($img, $r, $g, $b);
        $textColor = imagecolorallocate($img, 255, 255, 255);
        imagefill($img, 0, 0, $bgColor);

        $letter = strtoupper(substr($storeName, 0, 1));
        $font = 5;
        $textWidth = imagefontwidth($font) * strlen($letter);
        $textHeight = imagefontheight($font);
        imagestring($img, $font, (int) (($size - $textWidth) / 2), (int) (($size - $textHeight) / 2), $letter, $textColor);

        ob_start();
        imagepng($img);
        $data = ob_get_clean();
        imagedestroy($img);

        return response($data)
            ->header('Content-Type', 'image/png')
            ->header('Cache-Control', 'public, max-age=86400');
    }
}
