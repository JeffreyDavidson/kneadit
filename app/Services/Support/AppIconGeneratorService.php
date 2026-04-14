<?php

namespace App\Services\Support;

use Illuminate\Support\Str;

class AppIconGeneratorService
{
    public function generate(int $size, string $hexColor, string $storeName): string
    {
        $img = imagecreatetruecolor($size, $size);
        $r = hexdec(substr($hexColor, 1, 2));
        $g = hexdec(substr($hexColor, 3, 2));
        $b = hexdec(substr($hexColor, 5, 2));
        $bgColor = imagecolorallocate($img, min(255, max(0, (int) $r)), min(255, max(0, (int) $g)), min(255, max(0, (int) $b)));
        $textColor = imagecolorallocate($img, 255, 255, 255);
        imagefill($img, 0, 0, (int) $bgColor);

        $letter = Str::upper(substr($storeName, 0, 1));
        $font = 5;
        $textWidth = imagefontwidth($font) * strlen($letter);
        $textHeight = imagefontheight($font);
        imagestring($img, $font, (int) (($size - $textWidth) / 2), (int) (($size - $textHeight) / 2), $letter, (int) $textColor);

        ob_start();
        imagepng($img);
        $data = ob_get_clean();
        imagedestroy($img);

        return (string) $data;
    }
}
