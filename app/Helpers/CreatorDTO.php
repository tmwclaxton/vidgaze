<?php

namespace App\Helpers;

use App\Enums\Kind;
use App\Enums\Platform;
use App\Models\CreatorModels\Creator;
use App\Models\CreatorModels\CreatorSource;

class CreatorDTO
{
    public Kind $kind = Kind::Creator;

    public string $id;

    public string $name;

    public ?string $avatar_url;

    public ?string $banner_url;

    public ?string $description;

    public string $twitch_login;

    public bool $is_live;

    public Platform $platform;

    public ?string $region;

    public ?string $language;

    public function __construct(Platform $platform, string $id)
    {
        $this->platform = $platform;
        $this->id = $id;
    }

    public static function convertFromArray($creator) {}

    public function save(): Creator
    {
        $existing = CreatorSource::where('source_name', '=', $this->platform->value)
            ->where('external_channel_id', '=', $this->id)
            ->first();

        if ($existing !== null) {
            $creator = $existing->creator()->firstOrFail();
            $this->mergeIncomingProfileOntoCreator($creator);

            return $creator;
        }

        $creator = Creator::create([
            'slug' => $this->platform->getPrefix().'_'.$this->id,
            'name' => $this->name,
            'avatar_url' => self::nullIfBlank($this->avatar_url ?? null),
            'banner_url' => self::nullIfBlank($this->banner_url ?? null),
            'description' => $this->description ?? null,
            'region' => $this->region ?? null,
            'language' => $this->language ?? null,
            'is_live' => $this->is_live ?? null,
        ]);
        CreatorSource::create([
            'source_name' => $this->platform->value,
            'external_channel_id' => $this->id,
            'creator_id' => $creator->id,
        ]);

        return $creator;
    }

    /**
     * Backfill avatar, banner, and text fields when search/API returns data the stored creator is missing.
     */
    private function mergeIncomingProfileOntoCreator(Creator $creator): void
    {
        $updates = [];
        $avatar = self::nullIfBlank($this->avatar_url ?? null);
        if ($avatar !== null && self::isBlank($creator->avatar_url)) {
            $updates['avatar_url'] = $avatar;
        }
        $banner = self::nullIfBlank($this->banner_url ?? null);
        if ($banner !== null && self::isBlank($creator->banner_url)) {
            $updates['banner_url'] = $banner;
        }
        $desc = isset($this->description) ? trim((string) $this->description) : '';
        if ($desc !== '' && self::isBlank($creator->description)) {
            $updates['description'] = $desc;
        }
        $name = isset($this->name) ? trim((string) $this->name) : '';
        if ($name !== '' && (self::isBlank($creator->name) || $creator->name === 'Unknown')) {
            $updates['name'] = $name;
        }
        if (isset($this->region) && $this->region !== null && self::isBlank($creator->region)) {
            $updates['region'] = $this->region;
        }
        if (isset($this->language) && $this->language !== null && self::isBlank($creator->language)) {
            $updates['language'] = $this->language;
        }
        if ($updates !== []) {
            $creator->update($updates);
        }
    }

    private static function isBlank(mixed $value): bool
    {
        if ($value === null) {
            return true;
        }

        return trim((string) $value) === '';
    }

    private static function nullIfBlank(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $t = trim($value);

        return $t === '' ? null : $t;
    }

    public static function convertFromStdClass($creator): CreatorDTO
    {
        $creator_dto = new self(Platform::fromValue($creator->platform), $creator->id);
        $creator_dto->name = $creator->name;
        if (isset($creator->avatar_url)) {
            $creator_dto->avatar_url = $creator->avatar_url;
        }
        if (isset($creator->banner_url)) {
            $creator_dto->banner_url = $creator->banner_url;
        }
        if (isset($creator->description)) {
            $creator_dto->description = $creator->description;
        }
        if (isset($creator->twitch_login)) {
            $creator_dto->twitch_login = $creator->twitch_login;
        }
        if (isset($creator->is_live)) {
            $creator_dto->is_live = $creator->is_live;
        }
        if (isset($creator->region)) {
            $creator_dto->region = $creator->region;
        }
        if (isset($creator->language)) {
            $creator_dto->language = $creator->language;
        }

        return $creator_dto;
    }
}
