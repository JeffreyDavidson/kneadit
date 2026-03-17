<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\SetUpTenantTest;

class StorefrontTest extends TestCase
{
    use RefreshDatabase;
    use SetUpTenantTest;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
    }

    /** @test */
    public function menu_page_loads_successfully(): void
    {
        $response = $this->withoutMiddleware($this->tenantMiddleware)->get(route('storefront.menu', [], false));
        $response->assertOk();
    }

    /** @test */
    public function menu_page_shows_store_name_from_settings(): void
    {
        Setting::set('store_name', 'Sweet Dreams Bakery');

        $response = $this->withoutMiddleware($this->tenantMiddleware)->get(route('storefront.menu', [], false));
        $response->assertOk();
        $response->assertSee('Sweet Dreams Bakery');
    }

    /** @test */
    public function menu_page_shows_categories_and_products(): void
    {
        $category = Category::create([
            'name' => 'Pastries',
            'slug' => 'pastries',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        Product::create([
            'name' => 'Croissant',
            'slug' => 'croissant',
            'price' => 4.50,
            'category_id' => $category->id,
            'is_active' => true,
        ]);

        $response = $this->withoutMiddleware($this->tenantMiddleware)->get(route('storefront.menu', [], false));
        $response->assertOk();
        $response->assertSee('Pastries');
        $response->assertSee('Croissant');
    }

    /** @test */
    public function menu_page_shows_products_with_prices(): void
    {
        $category = Category::create([
            'name' => 'Cakes',
            'slug' => 'cakes',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        Product::create([
            'name' => 'Red Velvet Cake',
            'slug' => 'red-velvet-cake',
            'price' => 35.00,
            'category_id' => $category->id,
            'is_active' => true,
        ]);

        $response = $this->withoutMiddleware($this->tenantMiddleware)->get(route('storefront.menu', [], false));
        $response->assertOk();
        $response->assertSee('Red Velvet Cake');
        $response->assertSee('35.00');
    }

    /** @test */
    public function menu_hides_inactive_categories(): void
    {
        Category::create([
            'name' => 'Seasonal Only',
            'slug' => 'seasonal-only',
            'is_active' => false,
            'sort_order' => 1,
        ]);

        $response = $this->withoutMiddleware($this->tenantMiddleware)->get(route('storefront.menu', [], false));
        $response->assertOk();
        $response->assertDontSee('Seasonal Only');
    }

    /** @test */
    public function menu_does_not_show_inactive_products(): void
    {
        $category = Category::create([
            'name' => 'Breads',
            'slug' => 'breads',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        Product::create([
            'name' => 'Hidden Bread',
            'slug' => 'hidden-bread',
            'price' => 5.00,
            'category_id' => $category->id,
            'is_active' => false,
        ]);

        $response = $this->withoutMiddleware($this->tenantMiddleware)->get(route('storefront.menu', [], false));
        $response->assertOk();
        $response->assertDontSee('Hidden Bread');
    }

    /** @test */
    public function about_page_loads(): void
    {
        $response = $this->withoutMiddleware($this->tenantMiddleware)->get(route('storefront.about', [], false));
        $response->assertOk();
    }

    /** @test */
    public function contact_page_loads(): void
    {
        $response = $this->withoutMiddleware($this->tenantMiddleware)->get(route('contact.show', [], false));
        $response->assertOk();
    }

    /** @test */
    public function contact_form_submission_works_with_valid_data(): void
    {
        $response = $this->withoutMiddleware($this->tenantMiddleware)->post(route('contact.store', [], false), [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'subject' => 'Question about orders',
            'message' => 'Do you offer gluten-free options?',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('contact_messages', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'subject' => 'Question about orders',
        ]);
    }

    /** @test */
    public function contact_form_validation_rejects_missing_name(): void
    {
        $response = $this->withoutMiddleware($this->tenantMiddleware)->post(route('contact.store', [], false), [
            'email' => 'jane@example.com',
            'subject' => 'Test',
            'message' => 'Test message',
        ]);

        $response->assertSessionHasErrors('name');
    }

    /** @test */
    public function contact_form_validation_rejects_missing_email(): void
    {
        $response = $this->withoutMiddleware($this->tenantMiddleware)->post(route('contact.store', [], false), [
            'name' => 'Jane',
            'subject' => 'Test',
            'message' => 'Test message',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function contact_form_validation_rejects_missing_subject(): void
    {
        $response = $this->withoutMiddleware($this->tenantMiddleware)->post(route('contact.store', [], false), [
            'name' => 'Jane',
            'email' => 'jane@example.com',
            'message' => 'Test message',
        ]);

        $response->assertSessionHasErrors('subject');
    }

    /** @test */
    public function contact_form_validation_rejects_missing_message(): void
    {
        $response = $this->withoutMiddleware($this->tenantMiddleware)->post(route('contact.store', [], false), [
            'name' => 'Jane',
            'email' => 'jane@example.com',
            'subject' => 'Test',
        ]);

        $response->assertSessionHasErrors('message');
    }
}
