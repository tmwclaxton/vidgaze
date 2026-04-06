<?php

namespace Tests\Unit;

use App\Enums\Platform;
use App\Support\PlatformRegistry;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PlatformRegistryTest extends TestCase
{
    #[Test]
    public function every_platform_enum_case_has_a_registry_entry(): void
    {
        foreach (Platform::cases() as $case) {
            $this->assertArrayHasKey(
                $case->value,
                PlatformRegistry::definitions(),
                "Missing platform_capabilities config for [{$case->value}]"
            );
        }
    }

    #[Test]
    public function supported_for_video_index_only_includes_valid_platform_classes(): void
    {
        foreach (PlatformRegistry::supportedForVideoIndex(true) as $platform) {
            $this->assertInstanceOf(Platform::class, $platform);
            $this->assertNotNull(
                PlatformRegistry::platformApiClass($platform),
                "supported_for_video_index includes {$platform->value} without platform_class"
            );
        }
    }

    #[Test]
    public function oauth_studio_platforms_expose_an_auth_class(): void
    {
        foreach (PlatformRegistry::connectableStudioPlatforms() as $row) {
            if ($row['link_type'] === 'oauth' && $row['oauth_available']) {
                $this->assertNotEmpty($row['id']);
                $enum = Platform::fromValue($row['id']);
                $this->assertNotNull(PlatformRegistry::authClassForLogin($enum));
            }
        }
    }
}
