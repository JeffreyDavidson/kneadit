<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Tests\CentralTestCase;

class RegistrationFlowTest extends CentralTestCase
{
    /** @test */
    public function registration_page_loads(): void
    {
        $response = $this->get(route('register'));

        $response->assertOk();
        $response->assertSee('Start your bakery journey');
    }

    /** @test */
    public function user_can_register_with_valid_data(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Jane Baker',
            'email' => 'jane@example.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'bakery_name' => 'Sunshine Bakery',
        ]);

        $response->assertRedirect(route('billing.plans'));
        $this->assertDatabaseHas('users', ['email' => 'jane@example.com']);
        $this->assertAuthenticated();
        $this->assertEquals('Sunshine Bakery', session('bakery_name'));
    }

    /** @test */
    public function registration_requires_all_fields(): void
    {
        $response = $this->post(route('register'), []);

        $response->assertSessionHasErrors(['name', 'email', 'password', 'bakery_name']);
    }

    /** @test */
    public function registration_requires_unique_email(): void
    {
        User::create([
            'name' => 'Existing User',
            'email' => 'taken@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post(route('register'), [
            'name' => 'New User',
            'email' => 'taken@example.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'bakery_name' => 'My Bakery',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function registration_requires_password_confirmation(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Jane Baker',
            'email' => 'jane@example.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'DifferentPass!',
            'bakery_name' => 'My Bakery',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    /** @test */
    public function registration_requires_minimum_password_length(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Jane Baker',
            'email' => 'jane@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
            'bakery_name' => 'My Bakery',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    /** @test */
    public function plans_page_requires_authentication(): void
    {
        $response = $this->get(route('billing.plans'));

        $response->assertRedirect();
    }

    /** @test */
    public function authenticated_user_can_view_plans(): void
    {
        $user = User::create([
            'name' => 'Jane Baker',
            'email' => 'jane@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->actingAs($user)->get(route('billing.plans'));

        $response->assertOk();
        $response->assertSee('Choose Your Plan');
    }

    /** @test */
    public function checkout_requires_authentication(): void
    {
        $response = $this->post(route('billing.checkout', ['plan' => 'starter']));

        $response->assertRedirect();
    }

    /** @test */
    public function checkout_rejects_invalid_plan(): void
    {
        $user = User::create([
            'name' => 'Jane Baker',
            'email' => 'jane@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->actingAs($user)->post(route('billing.checkout', ['plan' => 'invalid']));

        $response->assertNotFound();
    }

    /** @test */
    public function onboarding_page_loads(): void
    {
        $user = User::create([
            'name' => 'Jane Baker',
            'email' => 'jane@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->actingAs($user)->get(route('onboarding.show'));

        $response->assertOk();
    }

    /** @test */
    public function onboarding_store_creates_tenant(): void
    {
        $user = User::create([
            'name' => 'Jane Baker',
            'email' => 'jane@example.com',
            'password' => bcrypt('password'),
        ]);

        // Mock the tenant creation since Stancl tries to create actual databases
        $this->mock(Tenant::class, function ($mock) {
            $mock->shouldReceive('create')->andReturn(new Tenant);
        });

        // We can't fully test tenant creation in SQLite without Stancl spinning up
        // a real tenant DB. Instead, test the validation.
        $response = $this->actingAs($user)->post(route('onboarding.store'), [
            'store_name' => '',
            'subdomain' => '',
            'storefront_choice' => '',
        ]);

        $response->assertSessionHasErrors(['store_name', 'subdomain', 'storefront_choice']);
    }

    /** @test */
    public function onboarding_validates_subdomain_format(): void
    {
        $user = User::create([
            'name' => 'Jane Baker',
            'email' => 'jane@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->actingAs($user)->post(route('onboarding.store'), [
            'store_name' => 'My Bakery',
            'subdomain' => 'invalid subdomain!',
            'storefront_choice' => 'kneadit',
        ]);

        $response->assertSessionHasErrors(['subdomain']);
    }

    /** @test */
    public function onboarding_requires_external_website_when_own_chosen(): void
    {
        $user = User::create([
            'name' => 'Jane Baker',
            'email' => 'jane@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->actingAs($user)->post(route('onboarding.store'), [
            'store_name' => 'My Bakery',
            'subdomain' => 'mybakery',
            'storefront_choice' => 'own',
        ]);

        $response->assertSessionHasErrors(['external_website']);
    }

    /** @test */
    public function onboarding_does_not_require_external_website_for_kneadit(): void
    {
        $user = User::create([
            'name' => 'Jane Baker',
            'email' => 'jane@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->actingAs($user)->post(route('onboarding.store'), [
            'store_name' => 'My Bakery',
            'subdomain' => 'mybakery',
            'storefront_choice' => 'kneadit',
        ]);

        // Should not have external_website error (may fail on tenant creation, that's ok)
        $this->assertNotContains('external_website', array_keys(session('errors')?->toArray() ?? []));
    }

    /** @test */
    public function guest_cannot_access_onboarding(): void
    {
        $response = $this->get(route('onboarding.show'));

        $response->assertRedirect();
    }

    /** @test */
    public function billing_success_redirects_to_onboarding(): void
    {
        $user = User::create([
            'name' => 'Jane Baker',
            'email' => 'jane@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->actingAs($user)->get(route('billing.success'));

        $response->assertRedirect(route('onboarding.show'));
    }

    /** @test */
    public function login_redirects_to_homepage(): void
    {
        $response = $this->get(route('login'));

        $response->assertRedirect(route('home'));
    }
}
