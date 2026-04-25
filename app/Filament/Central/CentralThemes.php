<?php

namespace App\Filament\Central;

use Filament\Support\Colors\Color;

class CentralThemes
{
    public const array AVAILABLE = [
        'honey' => 'Honey — warm browns + gold',
        'slate' => 'Slate — cool gray + gold',
        'nord' => 'Nord — Polar Night + Frost cyan',
    ];

    public static function current(): string
    {
        // platformSettings() wins so the UI toggle takes effect immediately;
        // config('kneadit.central_theme') (env CENTRAL_THEME) is the boot default.
        $value = platformSettings('central_theme') ?? config('kneadit.central_theme');

        return array_key_exists($value, self::AVAILABLE) ? $value : 'honey';
    }

    public static function rootCss(): string
    {
        $current = self::current();

        $palette = match ($current) {
            'slate' => self::slate(),
            'nord' => self::nord(),
            default => self::honey(),
        };

        // Rebind Tailwind brand color tokens (used by x-central.* components
        // and `bg-honey`/`text-cinnamon`/etc. utilities) to the active palette.
        // theme.css defines them with literal hex at build time; this overrides
        // them at runtime so every theme picks them up consistently.
        return $palette . ':root{'
            . '--color-warm-black:var(--platform-900);'
            . '--color-espresso:var(--platform-800);'
            . '--color-cinnamon:var(--platform-400);'
            . '--color-honey:var(--accent);'
            . '--color-golden:var(--accent-light);'
            . '--color-butter:var(--platform-300);'
            . '--color-parchment:var(--platform-200);'
            . '}'
            . self::primaryCss($current);
    }

    /**
     * Emit Filament's `--primary-*` palette overrides so input focus rings,
     * primary buttons, links, and toggles pick up a theme-aligned hue
     * instead of the panel-config-time `Color::Amber` default.
     */
    private static function primaryCss(string $theme): string
    {
        $palette = match ($theme) {
            'nord' => Color::Cyan,
            'slate' => Color::Sky,
            default => Color::Amber,
        };

        $css = ':root{';
        foreach ($palette as $shade => $value) {
            $css .= "--primary-{$shade}:{$value};";
        }

        return $css . '}';
    }

    private static function honey(): string
    {
        return ':root{'
            . '--platform-950:#0c0a09;'
            . '--platform-900:#1c1410;'
            . '--platform-800:#2a1f18;'
            . '--platform-700:#3d2c1e;'
            . '--platform-600:#5c4333;'
            . '--platform-500:#d4920c;'
            . '--platform-400:#e8b04a;'
            . '--platform-300:#f5d88e;'
            . '--platform-200:#faf0d6;'
            . '--platform-100:#fdf8ed;'
            . '--platform-50:#fefcf5;'
            . '--accent:#d4920c;'
            . '--accent-light:#e8b04a;'
            . '--border-subtle:rgba(212,146,12,0.12);'
            . '--border-medium:rgba(212,146,12,0.2);'
            . '--hover-bg:rgba(212,146,12,0.06);'
            . '--active-bg:rgba(212,146,12,0.12);'
            . '--scrollbar-thumb:rgba(212,146,12,0.2);'
            . '--focus-ring:rgba(212,146,12,0.15);'
            . '--thead-bg:rgba(212,146,12,0.06);'
            . '}';
    }

    private static function nord(): string
    {
        // Polar Night surfaces (#2e3440-#4c566a) + Frost cyan (#88c0d0) accent
        // and Snow Storm (#d8dee9-#eceff4) text. The iconic Nord palette.
        return ':root{'
            . '--platform-950:#2e3440;'
            . '--platform-900:#3b4252;'
            . '--platform-800:#434c5e;'
            . '--platform-700:#4c566a;'
            . '--platform-600:#5b6478;'
            . '--platform-500:#88c0d0;'
            . '--platform-400:#d8dee9;'
            . '--platform-300:#e5e9f0;'
            . '--platform-200:#eceff4;'
            . '--platform-100:#f0f3f7;'
            . '--platform-50:#f5f7fa;'
            . '--accent:#88c0d0;'
            . '--accent-light:#a3d4e0;'
            . '--border-subtle:rgba(136,192,208,0.12);'
            . '--border-medium:rgba(136,192,208,0.2);'
            . '--hover-bg:rgba(136,192,208,0.06);'
            . '--active-bg:rgba(136,192,208,0.15);'
            . '--scrollbar-thumb:rgba(136,192,208,0.25);'
            . '--focus-ring:rgba(136,192,208,0.2);'
            . '--thead-bg:rgba(136,192,208,0.06);'
            . '}';
    }

    private static function slate(): string
    {
        // Cool neutral slate surfaces with the brand gold accent retained for
        // primary buttons and active states — that gold-on-slate keeps the
        // KneadIt brand recognisable without the warm-warm clash.
        return ':root{'
            . '--platform-950:#0f172a;'
            . '--platform-900:#1e293b;'
            . '--platform-800:#334155;'
            . '--platform-700:#475569;'
            . '--platform-600:#64748b;'
            . '--platform-500:#d4920c;'
            . '--platform-400:#94a3b8;'
            . '--platform-300:#cbd5e1;'
            . '--platform-200:#e2e8f0;'
            . '--platform-100:#f1f5f9;'
            . '--platform-50:#f8fafc;'
            . '--accent:#d4920c;'
            . '--accent-light:#e8b04a;'
            . '--border-subtle:rgba(148,163,184,0.12);'
            . '--border-medium:rgba(148,163,184,0.2);'
            . '--hover-bg:rgba(148,163,184,0.06);'
            . '--active-bg:rgba(212,146,12,0.12);'
            . '--scrollbar-thumb:rgba(148,163,184,0.25);'
            . '--focus-ring:rgba(212,146,12,0.15);'
            . '--thead-bg:rgba(148,163,184,0.06);'
            . '}';
    }
}
