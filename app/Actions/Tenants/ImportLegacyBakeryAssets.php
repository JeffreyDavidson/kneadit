<?php

namespace App\Actions\Tenants;

use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class ImportLegacyBakeryAssets
{
    /**
     * @param array<string, array<int, array<string, mixed>>> $data
     * @return array{data: array<string, array<int, array<string, mixed>>>, store_logo: string}
     */
    public function __invoke(array $data, string $assetDirectory, string $tenantId): array
    {
        $root = realpath($assetDirectory);

        if ($root === false || ! is_dir($root)) {
            throw new InvalidArgumentException('The legacy asset directory does not exist.');
        }

        $assets = [
            'store_logo' => 'images/logo.jpg',
            'hero_image' => 'images/hero-banner.jpg',
            'store_photo' => 'images/cassie-portrait.jpg',
        ];

        foreach ($data['products'] ?? [] as $product) {
            if (! empty($product['image'])) {
                $productId = $this->stringValue($product['id'] ?? null, 'product id');
                $assets["product_{$productId}"] = $this->stringValue($product['image'], 'product image');
            }
        }

        $sources = [];
        foreach ($assets as $key => $relativePath) {
            $sources[$key] = $this->resolveSource($root, $relativePath);
        }

        $destinations = [];
        foreach ($sources as $key => $source) {
            $destination = "tenants/{$tenantId}/bakery-on-biscotto/" . basename($source);
            $contents = file_get_contents($source);
            throw_if($contents === false, InvalidArgumentException::class, "The legacy asset [{$source}] could not be read.");

            throw_unless(
                Storage::disk('public')->put($destination, $contents),
                InvalidArgumentException::class,
                "The legacy asset [{$source}] could not be stored.",
            );

            $destinations[$key] = $destination;
        }

        foreach ($data['products'] ?? [] as $index => $product) {
            if (! empty($product['image'])) {
                $productId = $this->stringValue($product['id'] ?? null, 'product id');
                $data['products'][$index]['image'] = $destinations["product_{$productId}"];
            }
        }

        $settings = [
            'store_logo' => $destinations['store_logo'],
            'hero_image' => $destinations['hero_image'],
            'store_photo' => $destinations['store_photo'],
            'store_tagline' => 'With love and flour dust',
            'store_email' => 'bakeryonbiscotto@gmail.com',
            'store_address' => 'Davenport, FL',
            'business_tagline' => 'Freshly baked with love',
            'hero_tagline' => 'Where Sourdough Dreams Come True',
            'hero_primary_cta_text' => 'Explore Our Menu',
            'hero_secondary_cta_text' => 'Our Story',
            'about_us_text' => "I've always loved being in the kitchen, but bread changed everything. I wanted my family to have bread without processed ingredients and preservatives, and curiosity eventually led me to sourdough. What started as care packages for friends became Bakery on Biscotto. Everything is baked in the same kitchen where I cook for my husband and daughter, and nothing leaves our home that I wouldn't put on our own table. Baking is my art form, and every piece is made by hand, with care. — Cassie",
            'allergy_disclaimer' => 'While certain items may not contain allergens, they are produced in an environment where allergens could be present. Please proceed with caution.',
            'faq_items' => json_encode($this->faqItems(), JSON_THROW_ON_ERROR),
            'homepage_sections' => json_encode($this->homepageSections(), JSON_THROW_ON_ERROR),
            'social_media_links' => json_encode([
                'facebook' => 'https://facebook.com/bakeryonbiscotto',
                'instagram' => 'https://instagram.com/bakeryonbiscotto',
            ], JSON_THROW_ON_ERROR),
        ];

        foreach ($settings as $key => $value) {
            $data['settings'][] = ['key' => $key, 'value' => $value];
        }

        return ['data' => $data, 'store_logo' => $destinations['store_logo']];
    }

    private function stringValue(mixed $value, string $description): string
    {
        if (! is_string($value) && ! is_int($value)) {
            throw new InvalidArgumentException("The legacy {$description} must be a string-compatible value.");
        }

        return (string) $value;
    }

    private function resolveSource(string $root, string $relativePath): string
    {
        $source = realpath($root . DIRECTORY_SEPARATOR . ltrim($relativePath, DIRECTORY_SEPARATOR));

        if ($source === false || ! is_file($source) || ! str_starts_with($source, $root . DIRECTORY_SEPARATOR)) {
            throw new InvalidArgumentException("The legacy asset [{$relativePath}] is missing or outside the asset directory.");
        }

        return $source;
    }

    /** @return array<int, array{question: string, answer: string}> */
    private function faqItems(): array
    {
        return [
            ['question' => 'How do I order?', 'answer' => 'Use our online order page. Pick what you would like, choose pickup or delivery, and check out.'],
            ['question' => 'How far in advance should I order?', 'answer' => 'Please order at least two days ahead. Sourdough is a slow process, and every order is baked fresh.'],
            ['question' => 'Do you deliver?', 'answer' => 'Yes. Pickup is in Davenport, Florida, and delivery is available throughout the Four Corners and greater Orlando area for a mileage-based fee.'],
            ['question' => 'What if I need to cancel?', 'answer' => 'Cancellations at least 48 hours ahead receive a full refund. Cancellations 24–48 hours ahead receive a 50% refund. Orders cancelled with less than 24 hours notice are non-refundable.'],
            ['question' => 'What if I cannot pick up at my scheduled time?', 'answer' => 'Contact us as soon as possible to reschedule. Orders cannot be held longer than 24 hours.'],
            ['question' => 'Can I customize my order?', 'answer' => 'Small adjustments may be possible, but fully custom items outside the menu are not available.'],
            ['question' => 'Why sourdough?', 'answer' => 'Sourdough uses natural fermentation for simpler ingredients and better flavor—no shortcuts or additives.'],
        ];
    }

    /** @return array<string, array<string, int|string|bool>> */
    private function homepageSections(): array
    {
        return [
            'hero' => ['visible' => true, 'order' => 1],
            'about' => ['visible' => true, 'order' => 2],
            'featured_products' => ['visible' => true, 'order' => 3, 'count' => 6, 'title' => 'Fresh from the Oven', 'subtitle' => 'Handcrafted sourdough and baked goods'],
            'categories' => ['visible' => true, 'order' => 4, 'title' => 'What We Bake', 'subtitle' => 'Made by hand, with care'],
            'reviews' => ['visible' => true, 'order' => 5, 'count' => 3, 'title' => 'Kind Words', 'subtitle' => 'From the Bakery on Biscotto community'],
            'gallery' => ['visible' => false, 'order' => 6],
            'blog' => ['visible' => false, 'order' => 7],
            'cta' => ['visible' => true, 'order' => 8, 'heading' => 'Ready for Fresh-Baked Sourdough?', 'button_text' => 'Start Your Order'],
            'social' => ['visible' => false, 'order' => 9],
        ];
    }
}
