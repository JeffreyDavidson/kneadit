<?php

namespace Tests\Feature;

use App\Services\DescriptionGeneratorService;
use Tests\TestCase;

class DescriptionGeneratorTest extends TestCase
{
    protected DescriptionGeneratorService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DescriptionGeneratorService;
    }

    /** @test */
    public function service_exists(): void
    {
        $this->assertInstanceOf(DescriptionGeneratorService::class, $this->service);
    }

    /** @test */
    public function generate_returns_array_of_3_descriptions(): void
    {
        $result = $this->service->generate('Sourdough Bread', 'professional', 'medium', 'bread');

        $this->assertIsArray($result);
        $this->assertCount(3, $result);
    }

    /** @test */
    public function each_description_contains_product_name(): void
    {
        $result = $this->service->generate('Sourdough Bread', 'professional', 'medium', 'bread');

        foreach ($result as $description) {
            $this->assertStringContainsString('Sourdough Bread', $description);
        }
    }

    /** @test */
    public function different_tones_produce_different_text(): void
    {
        $professional = $this->service->generate('Croissant', 'professional', 'medium', 'pastry');
        $playful = $this->service->generate('Croissant', 'playful', 'medium', 'pastry');

        // At least one description should differ between tones
        $this->assertNotEquals($professional, $playful);
    }

    /** @test */
    public function short_length_produces_shorter_text(): void
    {
        $short = $this->service->generate('Sourdough', 'professional', 'short', 'bread');
        $long = $this->service->generate('Sourdough', 'professional', 'long', 'bread');

        $shortWords = str_word_count(implode(' ', $short));
        $longWords = str_word_count(implode(' ', $long));

        $this->assertLessThan($longWords, $shortWords);
    }

    /** @test */
    public function custom_count_returns_requested_number(): void
    {
        $result = $this->service->generate('Muffin', 'casual', 'medium', 'pastry', null, 2);

        $this->assertCount(2, $result);
    }

    /** @test */
    public function unknown_tone_falls_back_to_professional(): void
    {
        $result = $this->service->generate('Bread', 'nonexistent_tone', 'medium');

        $this->assertIsArray($result);
        $this->assertCount(3, $result);
    }

    /** @test */
    public function null_category_uses_default(): void
    {
        $result = $this->service->generate('Mystery Item', 'casual', 'medium');

        foreach ($result as $description) {
            $this->assertStringContainsString('Mystery Item', $description);
        }
    }
}
