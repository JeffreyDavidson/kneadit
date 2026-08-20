<?php

namespace App\Services\Content;

use Illuminate\Support\Arr;
use Illuminate\Support\Number;
use Illuminate\Support\Str;

class DescriptionGeneratorService
{
    /** @return array<int, string> */
    public function generate(string $product, string $tone, string $length, ?string $category = null, ?float $price = null, int $count = 3): array
    {
        $templates = $this->templatesForTone($tone);
        $adjectivePool = $this->adjectivesForCategory($category);

        $selectedTemplates = collect($templates)
            ->shuffle()
            ->take(min($count, count($templates)));

        $descriptions = [];
        foreach ($selectedTemplates as $template) {
            $adjective = $this->randomString($adjectivePool);
            $text = Str::of($template)->replace(
                ['{product}', '{category}', '{adjective}', '{price}'],
                [$product, $category ?: 'baked goods', $adjective, (string) ($price ? Number::currency($price) : '')],
            )->toString();
            $descriptions[] = $this->adjustLength($text, $length, $product, $category ?: 'baked goods', $adjectivePool);
        }

        return $descriptions;
    }

    /** @return array<int, string> */
    protected function templatesForTone(string $tone): array
    {
        /** @var array<string, array<int, string>> $tones */
        $tones = config('description-templates.tones', []);

        return $tones[Str::lower($tone)] ?? $tones['professional'];
    }

    /** @return array<int, string> */
    protected function adjectivesForCategory(?string $category): array
    {
        /** @var array<string, array<int, string>> $pools */
        $pools = config('description-templates.adjectives', []);
        /** @var array<int, string> $defaults */
        $defaults = config('description-templates.default_adjectives', []);

        return $pools[Str::lower($category ?? 'default')] ?? $defaults;
    }

    /** @param array<int, string> $adjectives */
    protected function adjustLength(string $base, string $length, string $product, string $category, array $adjectives): string
    {
        return match (Str::lower($length)) {
            'short' => $this->toShort($base),
            'long' => $this->toLong($base, $product, $category, $adjectives),
            default => $base,
        };
    }

    protected function toShort(string $text): string
    {
        $pos = strpos($text, '. ');
        if ($pos !== false) {
            return substr($text, 0, $pos + 1);
        }
        $pos = strpos($text, '! ');
        if ($pos !== false) {
            return substr($text, 0, $pos + 1);
        }

        return $text;
    }

    /** @param array<int, string> $adjectives */
    protected function toLong(string $base, string $product, string $category, array $adjectives): string
    {
        /** @var array<int, string> $extras */
        $extras = config('description-templates.long_extras', []);

        $adj = $this->randomString($adjectives);
        $selectedExtras = Arr::random($extras, 2);

        $replace = fn (string $s): string => Str::of($s)
            ->replace(['{product}', '{category}', '{adjective}'], [$product, $category, $adj])
            ->toString();

        return $base . ' ' . $replace(Arr::string($selectedExtras, 0)) . ' ' . $replace(Arr::string($selectedExtras, 1));
    }

    /** @param array<int, string> $values */
    private function randomString(array $values): string
    {
        return collect($values)->random();
    }
}
