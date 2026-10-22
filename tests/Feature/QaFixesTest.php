<?php

namespace Tests\Feature;

use App\Enums\Platform;
use App\Helpers\PlatformAPIs\PlatformCredentials;
use App\Helpers\SearchQueryDTO;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class QaFixesTest extends TestCase
{
    public function test_short_legal_urls_permanently_redirect(): void
    {
        $this->get('/terms')->assertStatus(301)->assertRedirect('/terms_of_service');
        $this->get('/privacy')->assertStatus(301)->assertRedirect('/privacy_policy');
    }

    public function test_unconfigured_platforms_are_skipped_with_one_warning(): void
    {
        config([
            'platforms.twitch.client_id' => null,
            'platforms.vimeo.client_id' => null,
            'platforms.dailymotion.client_key' => null,
        ]);

        Log::spy();

        $this->assertFalse(PlatformCredentials::ensure(Platform::Vimeo));
        $this->assertFalse(PlatformCredentials::ensure(Platform::Vimeo));
        $this->assertTrue(PlatformCredentials::ensure(Platform::YouTube));

        Log::shouldHaveReceived('warning')->once();
    }

    public function test_search_only_uses_configured_platforms(): void
    {
        config([
            'platforms.twitch.client_id' => null,
            'platforms.vimeo.client_id' => null,
            'platforms.dailymotion.client_key' => null,
        ]);

        $platforms = (new SearchQueryDTO('music'))->getPlatforms();

        $this->assertContains(Platform::YouTube, $platforms);
        $this->assertContains(Platform::Rumble, $platforms);
        $this->assertNotContains(Platform::Twitch, $platforms);
        $this->assertNotContains(Platform::Vimeo, $platforms);
        $this->assertNotContains(Platform::Dailymotion, $platforms);
    }

    public function test_verify_email_page_renders_without_a_session_user(): void
    {
        $this->withoutVite();

        $this->withCookie('auth_flag', 'true')
            ->get('/verify-email')
            ->assertOk();
    }

    public function test_guest_browsing_does_not_use_up_the_login_rate_limit(): void
    {
        for ($i = 0; $i < 12; $i++) {
            $this->getJson('/api/v1/search/suggestions?query=a');
        }

        $this->postJson('/api/v1/auth/login', ['email' => 'nobody@example.com', 'password' => 'x'])
            ->assertHeader('X-RateLimit-Limit', 10)
            ->assertHeader('X-RateLimit-Remaining', 9);
    }
}
