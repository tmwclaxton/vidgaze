<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Smoke tests for VidGaze web routes (Inertia + API-backed auth).
 * Default Breeze-style session POST tests were removed; login/register happen via api/v1/auth/*.
 */
class WebAuthPagesTest extends TestCase
{
    public static function publicPagePaths(): array
    {
        return [
            'home' => ['/'],
            'login' => ['/login'],
            'register' => ['/register'],
            'forgot-password' => ['/forgot-password'],
            'about' => ['/about'],
        ];
    }

    #[DataProvider('publicPagePaths')]
    public function test_public_inertia_pages_return_ok(string $path): void
    {
        $response = $this->get($path);

        $response->assertOk();
    }
}
