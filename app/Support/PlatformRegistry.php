<?php

namespace App\Support;

use App\Enums\Platform;
use App\Models\User;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class PlatformRegistry
{
    /** @return array<string, array<string, mixed>> */
    public static function definitions(): array
    {
        return config('platform_capabilities', []);
    }

    /** @param  array<string, mixed>  $definition */
    public static function definitionFor(Platform $platform): array
    {
        $key = $platform->value;
        $all = self::definitions();
        if (! isset($all[$key])) {
            throw new InvalidArgumentException("Missing platform_capabilities entry for [{$key}]");
        }

        return $all[$key];
    }

    public static function isValidSourceValue(string $value): bool
    {
        return isset(self::definitions()[$value]);
    }

    /**
     * Platforms indexed in video/search flows (subset of enum).
     *
     * @return Collection<int, Platform>
     */
    public static function supportedForVideoIndex(bool $asEnum = true, bool $asPrefix = false): Collection
    {
        $platforms = collect(self::definitions())
            ->filter(fn (array $d) => ! empty($d['supported_for_video_index']))
            ->keys()
            ->map(fn (string $value) => Platform::fromValue($value));

        if ($asPrefix) {
            return $platforms->map(fn (Platform $p) => $p->getPrefix());
        }
        if ($asEnum) {
            return $platforms;
        }

        return $platforms->map(fn (Platform $p) => $p->value);
    }

    /**
     * Platforms participating in Redis unified search (SearchPlatform jobs).
     *
     * @return Collection<int, Platform>
     */
    public static function unifiedSearchPlatforms(bool $asEnum = true, bool $asPrefix = false): Collection
    {
        $platforms = collect(self::definitions())
            ->filter(fn (array $d) => ! empty($d['unified_search']))
            ->keys()
            ->map(fn (string $value) => Platform::fromValue($value));

        if ($asPrefix) {
            return $platforms->map(fn (Platform $p) => $p->getPrefix());
        }
        if ($asEnum) {
            return $platforms;
        }

        return $platforms->map(fn (Platform $p) => $p->value);
    }

    /**
     * @return Collection<int, Platform>
     */
    public static function uploadable(bool $asEnum = true, bool $asPrefix = false): Collection
    {
        $platforms = collect(self::definitions())
            ->filter(fn (array $d) => ! empty($d['uploadable']))
            ->keys()
            ->map(fn (string $value) => Platform::fromValue($value));

        if ($asPrefix) {
            return $platforms->map(fn (Platform $p) => $p->getPrefix());
        }
        if ($asEnum) {
            return $platforms;
        }

        return $platforms->map(fn (Platform $p) => $p->value);
    }

    /**
     * Studio "Connect channels" entries (OAuth or claim).
     *
     * @return list<array{id: string, label: string, link_type: string, oauth_available: bool}>
     */
    public static function connectableStudioPlatforms(): array
    {
        $out = [];
        foreach (self::definitions() as $id => $def) {
            if (empty($def['studio_connect'])) {
                continue;
            }
            $linkType = $def['link_type'] ?? null;
            if (! in_array($linkType, ['oauth', 'claim'], true)) {
                continue;
            }
            $oauth = $linkType === 'oauth' && ! empty($def['auth_class']);
            $out[] = [
                'id' => $id,
                'label' => (string) $def['label'],
                'link_type' => $linkType,
                'oauth_available' => $oauth,
            ];
        }

        return $out;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function connectablePlatformsForUser(User $user): array
    {
        $creator = $user->creator()->with('sources')->first();
        $linked = [];
        if ($creator) {
            foreach ($creator->sources as $source) {
                $linked[$source->source_name] = $source->external_channel_id;
            }
        }

        $payload = [];
        foreach (self::connectableStudioPlatforms() as $row) {
            $id = $row['id'];
            $payload[] = array_merge($row, [
                'linked' => array_key_exists($id, $linked),
                'external_channel_id' => $linked[$id] ?? null,
            ]);
        }

        return $payload;
    }

    public static function authClassForLogin(Platform $platform): ?string
    {
        $def = self::definitionFor($platform);
        if (($def['link_type'] ?? null) !== 'oauth') {
            return null;
        }

        return $def['auth_class'] ?? null;
    }

    public static function isClaimPlatform(Platform $platform): bool
    {
        $def = self::definitionFor($platform);

        return ($def['link_type'] ?? null) === 'claim';
    }

    /**
     * @return class-string|null
     */
    public static function platformApiClass(Platform $platform): ?string
    {
        $def = self::definitionFor($platform);

        return $def['platform_class'] ?? null;
    }

    /**
     * Preferred-source / player labels keyed by lowercase platform value (for API docs + future frontend build).
     *
     * @return list<array{value: string, frontend_player: string|null}>
     */
    public static function embedCapablePlayers(): array
    {
        $out = [];
        foreach (self::definitions() as $value => $def) {
            if (empty($def['frontend_player'])) {
                continue;
            }
            $out[] = [
                'value' => $value,
                'frontend_player' => $def['frontend_player'],
            ];
        }

        return $out;
    }
}
