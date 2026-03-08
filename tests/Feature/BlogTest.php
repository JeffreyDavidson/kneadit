<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureStorefrontEnabled;
use App\Http\Middleware\TrackPageView;
use App\Models\BlogPost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomainOrSubdomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use Tests\TestCase;

class BlogTest extends TestCase
{
    use RefreshDatabase;

    protected array $tenantMiddleware;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.connections.central' => config('database.connections.sqlite')]);
        config(['tenancy.central_domains' => []]);
        $tenantMigrationPath = database_path('migrations/tenant');
        if (is_dir($tenantMigrationPath)) {
            $this->artisan('migrate', ['--path' => $tenantMigrationPath, '--realpath' => true]);
        }
        $this->tenantMiddleware = [
            InitializeTenancyByDomainOrSubdomain::class,
            PreventAccessFromCentralDomains::class,
            EnsureStorefrontEnabled::class,
            TrackPageView::class,
        ];
    }

    /** @test */
    public function blog_index_page_loads(): void
    {
        $response = $this->withoutMiddleware($this->tenantMiddleware)->get('/blog');
        $response->assertOk();
    }

    /** @test */
    public function blog_shows_only_published_posts(): void
    {
        BlogPost::create([
            'title' => 'Published Post',
            'body' => 'Content here',
            'is_published' => true,
            'published_at' => now()->subDay(),
        ]);

        BlogPost::create([
            'title' => 'Draft Post',
            'body' => 'Draft content',
            'is_published' => false,
            'published_at' => null,
        ]);

        $response = $this->withoutMiddleware($this->tenantMiddleware)->get('/blog');
        $response->assertOk();
        $response->assertSee('Published Post');
        $response->assertDontSee('Draft Post');
    }

    /** @test */
    public function blog_hides_draft_posts(): void
    {
        BlogPost::create([
            'title' => 'My Draft',
            'body' => 'Not ready yet',
            'is_published' => false,
        ]);

        $response = $this->withoutMiddleware($this->tenantMiddleware)->get('/blog');
        $response->assertOk();
        $response->assertDontSee('My Draft');
    }

    /** @test */
    public function blog_paginates_at_six_per_page(): void
    {
        for ($i = 1; $i <= 8; $i++) {
            BlogPost::create([
                'title' => "Post Number $i",
                'body' => "Body $i",
                'is_published' => true,
                'published_at' => now()->subDays($i),
            ]);
        }

        $response = $this->withoutMiddleware($this->tenantMiddleware)->get('/blog');
        $response->assertOk();
        // First page should have 6 posts, not all 8
        $response->assertSee('Post Number 1');
        $response->assertSee('Post Number 6');
        $response->assertDontSee('Post Number 7');
    }

    /** @test */
    public function individual_blog_post_loads_by_slug(): void
    {
        BlogPost::create([
            'title' => 'Sourdough Tips',
            'slug' => 'sourdough-tips',
            'body' => 'Here are some tips for sourdough.',
            'is_published' => true,
            'published_at' => now()->subDay(),
        ]);

        $response = $this->withoutMiddleware($this->tenantMiddleware)->get('/blog/sourdough-tips');
        $response->assertOk();
        $response->assertSee('Sourdough Tips');
    }

    /** @test */
    public function returns_404_for_nonexistent_slug(): void
    {
        $response = $this->withoutMiddleware($this->tenantMiddleware)->get('/blog/does-not-exist');
        $response->assertNotFound();
    }

    /** @test */
    public function returns_404_for_draft_post_slug(): void
    {
        BlogPost::create([
            'title' => 'Secret Draft',
            'slug' => 'secret-draft',
            'body' => 'Hidden content',
            'is_published' => false,
        ]);

        $response = $this->withoutMiddleware($this->tenantMiddleware)->get('/blog/secret-draft');
        $response->assertNotFound();
    }

    /** @test */
    public function rss_feed_returns_xml_content_type(): void
    {
        $response = $this->withoutMiddleware($this->tenantMiddleware)->get('/blog/feed.xml');
        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/rss+xml; charset=UTF-8');
    }

    /** @test */
    public function rss_feed_contains_published_posts(): void
    {
        BlogPost::create([
            'title' => 'Feed Post',
            'body' => 'Feed body',
            'is_published' => true,
            'published_at' => now()->subDay(),
        ]);

        $response = $this->withoutMiddleware($this->tenantMiddleware)->get('/blog/feed.xml');
        $response->assertOk();
        $response->assertSee('Feed Post');
    }
}
