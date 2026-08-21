<?php

namespace App\Services\Content;

use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Stringable;

class QrCodeService
{
    public function generateSvg(string $url, int $size, string $hexColor): string
    {
        $svg = QrCode::size($size)
            ->color(...$this->parseHexColor($hexColor))
            ->margin(1)
            ->generate($url);

        return $this->stringify($svg);
    }

    public function generatePng(string $url, int $size, string $hexColor): string
    {
        $png = QrCode::size($size)
            ->color(...$this->parseHexColor($hexColor))
            ->margin(1)
            ->format('png')
            ->generate($url);

        return $this->stringify($png);
    }

    /**
     * The vendor QrCode package may return its own HtmlString class (which
     * PHPStan can't resolve) or a raw string. Normalise to string via
     * __toString() when available.
     */
    private function stringify(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_string($value) || $value instanceof Stringable) {
            return Str::of($value)->toString();
        }

        return '';
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
