<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureStorefrontEnabled;
use App\Http\Middleware\TrackPageView;
use App\Models\CustomerPhoto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomainOrSubdomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use Tests\TestCase;

class CustomerPhotoTest extends TestCase
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
    public function gallery_page_loads(): void
    {
        $response = $this->withoutMiddleware($this->tenantMiddleware)->get('/gallery');
        $response->assertOk();
    }

    /** @test */
    public function gallery_shows_only_approved_photos(): void
    {
        CustomerPhoto::create([
            'customer_name' => 'Alice',
            'customer_email' => 'alice@example.com',
            'caption' => 'My beautiful cake',
            'photo_path' => 'photos/approved.jpg',
            'is_approved' => true,
        ]);

        CustomerPhoto::create([
            'customer_name' => 'Bob',
            'customer_email' => 'bob@example.com',
            'caption' => 'Pending photo',
            'photo_path' => 'photos/pending.jpg',
            'is_approved' => false,
        ]);

        $response = $this->withoutMiddleware($this->tenantMiddleware)->get('/gallery');
        $response->assertOk();
        $response->assertSee('My beautiful cake');
        $response->assertDontSee('Pending photo');
    }

    /** @test */
    public function gallery_hides_unapproved_photos(): void
    {
        CustomerPhoto::create([
            'customer_name' => 'Eve',
            'customer_email' => 'eve@example.com',
            'caption' => 'Unapproved shot',
            'photo_path' => 'photos/unapproved.jpg',
            'is_approved' => false,
        ]);

        $response = $this->withoutMiddleware($this->tenantMiddleware)->get('/gallery');
        $response->assertOk();
        $response->assertDontSee('Unapproved shot');
    }

    /** @test */
    public function photo_submission_requires_name_and_email(): void
    {
        Storage::fake('public');

        $response = $this->withoutMiddleware($this->tenantMiddleware)->post('/gallery', [
            'photo' => UploadedFile::fake()->image('cake.jpg'),
        ]);

        $response->assertSessionHasErrors(['customer_name', 'customer_email']);
    }

    /** @test */
    public function photo_submission_saves_to_database_as_unapproved(): void
    {
        Storage::fake('public');

        $response = $this->withoutMiddleware($this->tenantMiddleware)->post('/gallery', [
            'customer_name' => 'Carol',
            'customer_email' => 'carol@example.com',
            'caption' => 'My order arrived!',
            'photo' => UploadedFile::fake()->image('order.jpg'),
        ]);

        $photo = CustomerPhoto::first();
        $this->assertNotNull($photo);
        $this->assertFalse($photo->is_approved);
        $this->assertEquals('Carol', $photo->customer_name);
    }
}
