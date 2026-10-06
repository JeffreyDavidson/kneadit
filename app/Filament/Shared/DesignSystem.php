<?php

declare(strict_types=1);

namespace App\Filament\Shared;

use Filament\Enums\ThemeMode;
use Filament\FontProviders\GoogleFontProvider;
use Filament\Panel;
use Filament\Support\Colors\Color;
use InvalidArgumentException;

/**
 * The KneadIt design system settings both admin panels share: fonts, colour
 * palettes and the light/dark switch. The colours and fonts mirror
 * resources/css/kneadit/tokens.css.
 */
final class DesignSystem
{
    public const string FONT_SANS = 'Instrument Sans';

    public const string FONT_DISPLAY = 'Young Serif';

    public const string HONEY = '#d4920c';

    public const string DANGER = '#a83248';

    public const string SUCCESS = '#4f6e4f';

    public const string WARNING = '#8a5a0a';

    public const string INFO = '#2f5f7a';

    public const string GRAY = '#7a5a3a';

    /**
     * Young Serif has a single weight, and Google Fonts rejects a request for
     * weights a family doesn't have, so the default weight list can't be used.
     */
    private const string FONT_DISPLAY_URL = 'https://fonts.googleapis.com/css2?family=Young+Serif&display=swap';

    public static function configure(Panel $panel): Panel
    {
        return $panel
            ->font(self::FONT_SANS, provider: GoogleFontProvider::class)
            ->serifFont(self::FONT_DISPLAY, url: self::FONT_DISPLAY_URL, provider: GoogleFontProvider::class)
            ->colors([
                'primary' => Color::hex(self::HONEY),
                'danger' => Color::hex(self::DANGER),
                'success' => Color::hex(self::SUCCESS),
                'warning' => Color::hex(self::WARNING),
                'info' => Color::hex(self::INFO),
                'gray' => self::warmGray(),
            ])
            ->darkMode()
            ->defaultThemeMode(ThemeMode::Dark);
    }

    /**
     * The gray palette Filament uses for text, borders and surfaces. Color::hex()
     * gives every colour the same chroma curve, which at its brown hue turns
     * "gray" text bright orange. This keeps the hue and lightness steps of
     * Color::hex() but scales the chroma so shade 500 carries only the source
     * colour's own chroma, which makes it a warm neutral.
     *
     * @return array<int, string>
     */
    private static function warmGray(): array
    {
        [, $sourceChroma] = self::oklch(Color::convertToOklch(self::GRAY));

        $palette = Color::hex(self::GRAY);
        [, $baseChroma] = self::oklch($palette[500]);

        return array_map(
            function (string $shade) use ($sourceChroma, $baseChroma): string {
                [$lightness, $chroma, $hue] = self::oklch($shade);

                return sprintf('oklch(%.4f %.4f %.3f)', $lightness, $chroma * $sourceChroma / $baseChroma, $hue);
            },
            $palette,
        );
    }

    /**
     * @return array{float, float, float} Lightness, chroma and hue.
     */
    private static function oklch(string $color): array
    {
        if (preg_match('/^oklch\(([-+\d.eE]+) ([-+\d.eE]+) ([-+\d.eE]+)\)$/', $color, $matches) !== 1) {
            throw new InvalidArgumentException("Not an oklch colour: {$color}");
        }

        return [(float) $matches[1], (float) $matches[2], (float) $matches[3]];
    }
}
