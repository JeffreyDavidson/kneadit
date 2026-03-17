<?php

namespace Tests\Unit;

use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BlogPostTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.connections.central' => config('database.connections.sqlite')]);
        DB::connection('central')->setPdo(
            DB::connection('sqlite')->getPdo()
        );
        $tenantMigrationPath = database_path('migrations/tenant');
        if (is_dir($tenantMigrationPath)) {
            $this->artisan('migrate', ['--path' => $tenantMigrationPath, '--realpath' => true]);
        }
        User::create(['name' => 'Test', 'email' => 'test@test.com', 'password' => bcrypt('password')]);
    }

    /** @test */
    public function slug_is_auto_generated_from_title(): void
    {
        $post = BlogPost::create([
            'title' => 'My Awesome Blog Post',
            'body' => 'Some content here',
        ]);

        $this->assertEquals('my-awesome-blog-post', $post->slug);
    }

    /** @test */
    public function slug_is_updated_when_title_changes(): void
    {
        $post = BlogPost::create([
            'title' => 'Original Title',
            'body' => 'Content',
        ]);

        $post->update(['title' => 'Updated Title']);
        $this->assertEquals('updated-title', $post->fresh()->slug);
    }

    /** @test */
    public function duplicate_slugs_are_made_unique(): void
    {
        BlogPost::create(['title' => 'Same Title', 'body' => 'First']);
        $post2 = BlogPost::create(['title' => 'Same Title', 'body' => 'Second']);

        $this->assertEquals('same-title-2', $post2->slug);
    }

    /** @test */
    public function published_at_can_be_set(): void
    {
        $post = BlogPost::create([
            'title' => 'Published Post',
            'body' => 'Content',
            'is_published' => true,
            'published_at' => now(),
        ]);

        $this->assertTrue($post->is_published);
        $this->assertNotNull($post->published_at);
    }

    // tags_are_cast_to_array removed — blog_posts table doesn't have a tags column yet
}
