<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class VerificationEmailTest extends TestCase
{
    use RefreshDatabase;

    public function createApplication(): \Illuminate\Foundation\Application
    {
        // run against in-memory sqlite so CI needs no database service
        foreach (['DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => ':memory:'] as $k => $v) {
            putenv("$k=$v");
            $_ENV[$k] = $v;
            $_SERVER[$k] = $v;
        }

        return parent::createApplication();
    }

    private function register(string $email = 'new-user@example.com')
    {
        return $this->postJson('/api/v1/auth/register', [
            'username' => 'newuser',
            'email' => $email,
            'password' => 'Secret-pass-123',
            'password_confirmation' => 'Secret-pass-123',
            'terms' => true,
        ]);
    }

    public function test_registration_sends_the_verification_email(): void
    {
        Notification::fake();

        $this->register()->assertCreated()->assertJsonStructure(['access_token']);

        $user = User::where('email', 'new-user@example.com')->firstOrFail();
        $this->assertNull($user->email_verified_at);
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_registration_still_succeeds_when_mail_fails(): void
    {
        Notification::shouldReceive('send')->andThrow(new \RuntimeException('smtp down'));

        $this->register('mail-down@example.com')->assertCreated();
        $this->assertDatabaseHas('users', ['email' => 'mail-down@example.com']);
    }

    public function test_unverified_user_can_resend_without_sending_an_email_field(): void
    {
        Notification::fake();
        $token = $this->register()->json('access_token');
        $user = User::where('email', 'new-user@example.com')->firstOrFail();

        $this->withToken($token)->postJson('/api/v1/auth/email/verify')
            ->assertOk()
            ->assertJson(['message' => 'Email verification link sent']);

        Notification::assertSentToTimes($user, VerifyEmail::class, 2);
    }

    public function test_unverified_user_can_still_use_authenticated_endpoints(): void
    {
        Notification::fake();
        $token = $this->register()->json('access_token');

        $this->withToken($token)->getJson('/api/v1/auth/user')->assertOk();
    }
}
