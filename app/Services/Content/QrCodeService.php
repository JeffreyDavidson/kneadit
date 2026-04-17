<?php

namespace App\Services\Content;

use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrCodeService
{
    public function generateSvg(string $url, int $size, string $hexColor): string
    {
        $svg = QrCode::size($size)
            ->color(...$this->parseHexColor($hexColor))
            ->margin(1)
            ->generate($url);

        return (string) ($svg ?? '');
    }

    public function generatePng(string $url, int $size, string $hexColor): string
    {
        $png = QrCode::size($size)
            ->color(...$this->parseHexColor($hexColor))
            ->margin(1)
            ->format('png')
            ->generate($url);

        return (string) ($png ?? '');
    }

    /**
     * @return array{0: int, 1: int, 2: int}
     */
    private function parseHexColor(string $hexColor): array
    {
        $hex = ltrim($hexColor, '#');

        return [
            (int) hexdec(substr($hex, 0, 2)),
            (int) hexdec(substr($hex, 2, 2)),
            (int) hexdec(substr($hex, 4, 2)),
        ];
    }
}
