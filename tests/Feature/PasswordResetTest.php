<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\CentralTestCase;

class PasswordResetTest extends CentralTestCase
{
    public function test_forgot_password_page_loads(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertOk();
        $response->assertSee('Reset your password');
    }

    public function test_reset_link_can_be_requested(): void
    {
        Notification::fake();

        $user = User::create([
            'name' => 'Test Baker',
            'email' => 'baker@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->post('/forgot-password', ['email' => 'baker@example.com']);

        $response->assertSessionHas('status');
        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_reset_link_not_sent_for_invalid_email(): void
    {
        Notification::fake();

        $response = $this->post('/forgot-password', ['email' => 'nobody@example.com']);

        Notification::assertNothingSent();
    }

    public function test_reset_link_requires_email(): void
    {
        $response = $this->post('/forgot-password', []);

        $response->assertSessionHasErrors('email');
    }

    public function test_password_can_be_reset(): void
    {
        $user = User::create([
            'name' => 'Test Baker',
            'email' => 'baker@example.com',
            'password' => Hash::make('old-password'),
        ]);

        $token = Password::createToken($user);

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => 'baker@example.com',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertRedirect('/login');
        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
    }

    public function test_reset_password_page_loads(): void
    {
        $response = $this->get('/reset-password/fake-token?email=baker@example.com');

        $response->assertOk();
        $response->assertSee('Set new password');
    }

    public function test_reset_requires_valid_token(): void
    {
        $user = User::create([
            'name' => 'Test Baker',
            'email' => 'baker@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->post('/reset-password', [
            'token' => 'invalid-token',
            'email' => 'baker@example.com',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_reset_requires_password_confirmation(): void
    {
        $response = $this->post('/reset-password', [
            'token' => 'some-token',
            'email' => 'baker@example.com',
            'password' => 'new-password',
            'password_confirmation' => 'wrong-confirmation',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_register_page_has_forgot_password_link(): void
    {
        $response = $this->get('/register');

        $response->assertOk();
        $response->assertSee('forgot-password');
    }
}
