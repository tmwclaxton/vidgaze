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
        $app = parent::createApplication();
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite.database', ':memory:');

        return $app;
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

    private function verifyUrl(User $user, ?string $hash = null, int $minutes = 60): string
    {
        return \Illuminate\Support\Facades\URL::temporarySignedRoute('verification.verify', now()->addMinutes($minutes), [
            'id' => $user->getKey(),
            'hash' => $hash ?? sha1($user->getEmailForVerification()),
        ]);
    }

    private function unverifiedUser(): User
    {
        $this->withoutVite();
        config(['view.compiled' => sys_get_temp_dir()]);
        $user = User::factory()->create(['email_verified_at' => null]);

        return $user;
    }

    public function test_signed_link_verifies_without_session_or_token(): void
    {
        $user = $this->unverifiedUser();

        $this->get($this->verifyUrl($user))->assertOk()
            ->assertInertia(fn ($page) => $page->component('Auth/VerifyingEmail')->where('status', 'verified'));

        $this->assertNotNull($user->fresh()->email_verified_at);
        $this->assertGuest();
    }

    public function test_already_verified_link_reports_already_verified(): void
    {
        $user = $this->unverifiedUser();
        $user->markEmailAsVerified();

        $this->get($this->verifyUrl($user))->assertOk()
            ->assertInertia(fn ($page) => $page->where('status', 'already-verified'));
    }

    public function test_wrong_hash_does_not_verify(): void
    {
        $user = $this->unverifiedUser();

        $this->get($this->verifyUrl($user, sha1('someone-else@example.com')))->assertOk()
            ->assertInertia(fn ($page) => $page->where('status', 'invalid'));

        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_tampered_signature_is_rejected(): void
    {
        $user = $this->unverifiedUser();

        $this->get($this->verifyUrl($user).'x')->assertForbidden();
        $this->assertNull($user->fresh()->email_verified_at);
    }

    public function test_expired_link_is_rejected(): void
    {
        $user = $this->unverifiedUser();
        $url = $this->verifyUrl($user, null, 1);
        $this->travel(2)->minutes();

        $this->get($url)->assertForbidden();
        $this->assertNull($user->fresh()->email_verified_at);
    }
}
