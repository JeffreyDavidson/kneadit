<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\SocialPost;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SocialPostTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.connections.central' => config('database.connections.sqlite')]);
        $tenantMigrationPath = database_path('migrations/tenant');
        if (is_dir($tenantMigrationPath)) {
            $this->artisan('migrate', ['--path' => $tenantMigrationPath, '--realpath' => true]);
        }
    }

    public function test_social_post_model_exists(): void
    {
        $this->assertTrue(class_exists(SocialPost::class));
    }

    public function test_social_post_can_be_created(): void
    {
        $post = SocialPost::create([
            'platform' => 'instagram',
            'caption' => 'Check out our new sourdough! 🍞',
            'status' => 'draft',
        ]);

        $this->assertDatabaseHas('social_posts', [
            'platform' => 'instagram',
            'caption' => 'Check out our new sourdough! 🍞',
        ]);
    }

    public function test_platform_max_lengths_are_defined(): void
    {
        $this->assertEquals(2200, SocialPost::PLATFORM_MAX_CHARS['instagram']);
        $this->assertEquals(63206, SocialPost::PLATFORM_MAX_CHARS['facebook']);
        $this->assertEquals(4000, SocialPost::PLATFORM_MAX_CHARS['tiktok']);
    }

    public function test_platforms_are_defined(): void
    {
        $this->assertArrayHasKey('instagram', SocialPost::PLATFORMS);
        $this->assertArrayHasKey('facebook', SocialPost::PLATFORMS);
        $this->assertArrayHasKey('tiktok', SocialPost::PLATFORMS);
    }

    public function test_status_defaults_to_draft(): void
    {
        $post = SocialPost::create([
            'platform' => 'facebook',
            'caption' => 'Test post',
            'status' => 'draft',
        ]);

        $this->assertEquals('draft', $post->status);
    }

    public function test_statuses_are_defined(): void
    {
        $this->assertArrayHasKey('draft', SocialPost::STATUSES);
        $this->assertArrayHasKey('scheduled', SocialPost::STATUSES);
        $this->assertArrayHasKey('posted', SocialPost::STATUSES);
    }

    public function test_scheduled_post_has_scheduled_for_date(): void
    {
        $scheduledDate = now()->addDays(3);

        $post = SocialPost::create([
            'platform' => 'instagram',
            'caption' => 'Scheduled post',
            'status' => 'scheduled',
            'scheduled_for' => $scheduledDate,
        ]);

        $this->assertNotNull($post->scheduled_for);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $post->scheduled_for);
    }

    public function test_social_post_belongs_to_product(): void
    {
        $category = Category::create(['name' => 'Bread', 'slug' => 'bread', 'sort_order' => 0]);
        $product = Product::create([
            'name' => 'Sourdough',
            'slug' => 'sourdough',
            'price' => 8.99,
            'category_id' => $category->id,
            'is_active' => true,
        ]);

        $post = SocialPost::create([
            'platform' => 'instagram',
            'caption' => 'Our famous sourdough',
            'product_id' => $product->id,
            'status' => 'draft',
        ]);

        $this->assertEquals($product->id, $post->product->id);
    }
}
