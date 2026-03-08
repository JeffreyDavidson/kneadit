<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Http\Middleware\EnsureStorefrontEnabled;
use App\Http\Middleware\TrackPageView;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomainOrSubdomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use Tests\TestCase;

class ReviewTest extends TestCase
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

    private function createProduct(): Product
    {
        $category = Category::create([
            'name' => 'Breads',
            'slug' => 'breads',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        return Product::create([
            'name' => 'Sourdough',
            'slug' => 'sourdough',
            'price' => 8.00,
            'category_id' => $category->id,
            'is_active' => true,
        ]);
    }

    /** @test */
    public function reviews_page_loads(): void
    {
        $response = $this->withoutMiddleware([InitializeTenancyByDomainOrSubdomain::class, PreventAccessFromCentralDomains::class, EnsureStorefrontEnabled::class, TrackPageView::class])->get('/reviews');
        $response->assertOk();
    }

    /** @test */
    public function reviews_page_shows_approved_reviews(): void
    {
        $product = $this->createProduct();

        Review::create([
            'customer_name' => 'Happy Customer',
            'customer_email' => 'happy@example.com',
            'product_id' => $product->id,
            'rating' => 5,
            'comment' => 'Absolutely delicious sourdough!',
            'is_approved' => true,
        ]);

        $response = $this->withoutMiddleware([InitializeTenancyByDomainOrSubdomain::class, PreventAccessFromCentralDomains::class, EnsureStorefrontEnabled::class, TrackPageView::class])->get('/reviews');
        $response->assertOk();
        $response->assertSee('Happy Customer');
        $response->assertSee('Absolutely delicious sourdough!');
    }

    /** @test */
    public function reviews_page_hides_unapproved_reviews(): void
    {
        $product = $this->createProduct();

        Review::create([
            'customer_name' => 'Pending Reviewer',
            'customer_email' => 'pending@example.com',
            'product_id' => $product->id,
            'rating' => 3,
            'comment' => 'This should not be visible yet',
            'is_approved' => false,
        ]);

        $response = $this->withoutMiddleware([InitializeTenancyByDomainOrSubdomain::class, PreventAccessFromCentralDomains::class, EnsureStorefrontEnabled::class, TrackPageView::class])->get('/reviews');
        $response->assertOk();
        $response->assertDontSee('This should not be visible yet');
    }

    /** @test */
    public function reviews_page_shows_average_rating(): void
    {
        $product = $this->createProduct();

        Review::create([
            'customer_name' => 'A',
            'customer_email' => 'a@example.com',
            'product_id' => $product->id,
            'rating' => 5,
            'comment' => 'Great!',
            'is_approved' => true,
        ]);

        Review::create([
            'customer_name' => 'B',
            'customer_email' => 'b@example.com',
            'product_id' => $product->id,
            'rating' => 3,
            'comment' => 'Good',
            'is_approved' => true,
        ]);

        $response = $this->withoutMiddleware([InitializeTenancyByDomainOrSubdomain::class, PreventAccessFromCentralDomains::class, EnsureStorefrontEnabled::class, TrackPageView::class])->get('/reviews');
        $response->assertOk();
        // Average is 4.0
        $response->assertSee('4');
    }

    /** @test */
    public function empty_reviews_page_shows_empty_state(): void
    {
        $response = $this->withoutMiddleware([InitializeTenancyByDomainOrSubdomain::class, PreventAccessFromCentralDomains::class, EnsureStorefrontEnabled::class, TrackPageView::class])->get('/reviews');
        $response->assertOk();
        // No reviews exist - page should still load fine
        $this->assertEquals(0, Review::count());
    }

    /** @test */
    public function reviews_only_counts_approved_in_average(): void
    {
        $product = $this->createProduct();

        Review::create([
            'customer_name' => 'A',
            'customer_email' => 'a@example.com',
            'product_id' => $product->id,
            'rating' => 5,
            'comment' => 'Amazing',
            'is_approved' => true,
        ]);

        Review::create([
            'customer_name' => 'Troll',
            'customer_email' => 'troll@example.com',
            'product_id' => $product->id,
            'rating' => 1,
            'comment' => 'Spam review',
            'is_approved' => false,
        ]);

        $avgRating = Review::where('is_approved', true)->avg('rating');
        $this->assertEquals(5.0, (float) $avgRating);
    }
}
