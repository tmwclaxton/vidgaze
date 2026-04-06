<?php

namespace App\Helpers;

use App\Enums\Audience;
use App\Enums\Kind;
use App\Enums\Platform;
use App\Models\Category;
use App\Models\PodcastModels\Podcast;
use App\Models\StreamModels\Stream;
use App\Models\StreamModels\StreamSource;
use App\Models\VideoModels\Video;
use App\Models\VideoModels\VideoSource;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ContentDTO
{
    public ContentDTO $category;

    public array $podcast_episodes;

    public Kind|string $kind;

    public Platform|string $platform;

    public string $creator_id;

    public string $category_slug;

    public bool $assignable;

    public int $dislikes;

    public Carbon $publish_time;

    public Carbon $upload_time;

    public int $views;

    public int $likes;

    public string $thumbnail_url;

    public ?string $description;

    // public $bio;
    public ?string $region;

    public ?string $language;

    public array $tags;

    public string $name;

    public string $twitch_login;

    public string $duration;

    //    public bool $explicit;
    public Audience $audience;

    //    Podcast Specific Variables
    public string $audio_url;

    public string $rss_url;

    public string $guid;

    public string $subcategory_name;

    public string $audio_file_type;

    public int $result_index;

    public string $id;

    public bool $is_live;

    public function __construct(Platform $platform, Kind $kind, string $id)
    {
        $this->platform = $platform;
        $this->kind = $kind;
        $this->id = $id;
    }

    public function save($creator_id)
    {
        return match ($this->kind) {
            Kind::Video => $this->saveVideo($creator_id),
            Kind::Stream => $this->saveStream($creator_id),
            Kind::Podcast => $this->savePodcast($creator_id),
            default => throw new \Exception('Invalid Kind'),
        };
    }

    public function saveCategory()
    {
        $platform_category_id_column_name = $this->platform->getCategoryIdAttribute();

        return Category::firstOrCreate([
            $platform_category_id_column_name => $this->id,
        ], [
            'slug' => $this->category_slug,
            'name' => $this->name,
            'description' => $this->description ?? null,
            'thumbnail_url' => $this->thumbnail_url ?? null,
        ]);
    }

    public static function saveAll(array $content_dtos, $creator_id): array
    {
        $models = [];
        foreach ($content_dtos as $content_dto) {
            $models[] = $content_dto->save($creator_id);
        }

        return $models;
    }

    public function saveVideo($creator_id): Video
    {
        $existing = VideoSource::where('source_name', '=', $this->platform->value)
            ->where('external_id', '=', $this->id)
            ->first();

        if ($existing !== null) {
            $video = $existing->video()->firstOrFail();
            $this->mergeVideoMetadataOnto($video);

            return $video;
        }

        $video = Video::create([
            'slug' => $this->platform->getPrefix().'_'.$this->id,
            'title' => $this->name,
            'description' => $this->description ?? null,
            'thumbnail_url' => $this->thumbnail_url ?? null,
            'duration' => (int) $this->duration,
            'time_uploaded' => $this->upload_time ?? null,
            'time_published' => $this->publish_time ?? null,
            'region' => $this->region ?? null,
            'language' => $this->language ?? null,
            'tags' => json_encode($this->tags ?? null),
            'creator_id' => $creator_id,
            'preferred_source' => $this->platform->value,
            'audience' => $this->audience->value ?? Audience::ALL,
            'view_count' => isset($this->views) ? max(0, (int) $this->views) : 0,
            'like_count' => isset($this->likes) ? max(0, (int) $this->likes) : 0,
        ]);
        VideoSource::create([
            'source_name' => $this->platform->value,
            'external_id' => $this->id,
            'video_id' => $video->id,
        ]);

        return $video;
    }

    /**
     * Backfill title, text media, duration, timestamps, tags, and counts when Redis search returns richer data than the stored row.
     */
    private function mergeVideoMetadataOnto(Video $video): void
    {
        $updates = [];

        $thumb = trim((string) ($this->thumbnail_url ?? ''));
        if ($thumb !== '' && self::isBlankString($video->thumbnail_url ?? null)) {
            $updates['thumbnail_url'] = $thumb;
        }

        $desc = $this->description !== null ? trim((string) $this->description) : '';
        if ($desc !== '' && self::isBlankString($video->description)) {
            $updates['description'] = $desc;
        }

        $incomingDur = (int) $this->duration;
        if ($incomingDur > 0 && (int) $video->duration === 0) {
            $updates['duration'] = $incomingDur;
        }

        $incomingTitle = trim((string) ($this->name ?? ''));
        if ($incomingTitle !== '' && self::videoTitleLooksLikePlaceholder((string) $video->title)) {
            $updates['title'] = $incomingTitle;
        }

        if (isset($this->tags) && is_array($this->tags) && $this->tags !== [] && self::videoTagsAreEmpty($video)) {
            $updates['tags'] = json_encode($this->tags);
        }

        if (isset($this->publish_time) && $this->publish_time instanceof Carbon) {
            $stored = $video->time_published ? Carbon::parse($video->time_published) : null;
            if ($stored === null) {
                $updates['time_published'] = $this->publish_time;
            } elseif ($this->publish_time->lessThan($stored->copy()->subHours(6))) {
                $updates['time_published'] = $this->publish_time;
            }
        }

        if (isset($this->views) && (int) $this->views > 0 && (int) $video->view_count === 0) {
            $updates['view_count'] = (int) $this->views;
        }
        if (isset($this->likes) && (int) $this->likes > 0 && (int) $video->like_count === 0) {
            $updates['like_count'] = (int) $this->likes;
        }

        if (isset($this->region) && $this->region !== null && $this->region !== '' && self::isBlankString($video->region ?? null)) {
            $updates['region'] = $this->region;
        }
        if (isset($this->language) && $this->language !== null && $this->language !== '' && self::isBlankString($video->language ?? null)) {
            $updates['language'] = $this->language;
        }

        if ($updates !== []) {
            $video->update($updates);
        }
    }

    public function saveStream($creator_id): Stream
    {
        $existing = StreamSource::where('source_name', '=', $this->platform->value)
            ->where('external_id', '=', $this->id)
            ->first();

        if ($existing !== null) {
            $stream = $existing->stream()->firstOrFail();
            $this->mergeStreamMetadataOnto($stream);

            return $stream;
        }

        $stream = Stream::create([
            'slug' => $this->platform->getPrefix().'_'.$this->id,
            'title' => $this->name,
            'description' => $this->description ?? null,
            'thumbnail_url' => $this->thumbnail_url ?? null,
            'started_at' => $this->publish_time ?? null,
            'region' => $this->region ?? null,
            'language' => $this->language ?? null,
            'tags' => self::truncateStreamTagsJson($this->tags ?? null),
            'creator_id' => $creator_id,
            'preferred_source' => $this->platform->value,
            'audience' => $this->audience->value ?? Audience::ALL,
            'is_live' => $this->is_live ?? null,
            'category_id' => isset($this->category) ? $this->category->saveCategory()->id : null,
            'viewers' => isset($this->views) ? max(0, (int) $this->views) : 0,
        ]);
        StreamSource::create([
            'source_name' => $this->platform->value,
            'external_id' => $this->id,
            'stream_id' => $stream->id,
        ]);

        return $stream;
    }

    private function mergeStreamMetadataOnto(Stream $stream): void
    {
        $updates = [];

        $thumb = trim((string) ($this->thumbnail_url ?? ''));
        if ($thumb !== '' && self::isBlankString($stream->thumbnail_url ?? null)) {
            $updates['thumbnail_url'] = $thumb;
        }

        $desc = $this->description !== null ? trim((string) $this->description) : '';
        if ($desc !== '' && self::isBlankString($stream->description)) {
            $updates['description'] = self::truncateStreamDescription($desc);
        }

        if (isset($this->publish_time) && $this->publish_time instanceof Carbon) {
            $stored = $stream->started_at ? Carbon::parse($stream->started_at) : null;
            if ($stored === null) {
                $updates['started_at'] = $this->publish_time;
            } elseif ($this->publish_time->lessThan($stored->copy()->subHours(6))) {
                $updates['started_at'] = $this->publish_time;
            }
        }

        if (isset($this->views) && (int) $this->views > 0 && (int) ($stream->viewers ?? 0) === 0) {
            $updates['viewers'] = (int) $this->views;
        }

        if (isset($this->tags) && is_array($this->tags) && $this->tags !== [] && self::isBlankString($stream->tags ?? null)) {
            $updates['tags'] = self::truncateStreamTagsJson($this->tags);
        }

        if (isset($this->language) && $this->language !== null && $this->language !== '' && self::isBlankString($stream->language ?? null)) {
            $updates['language'] = $this->language;
        }
        if (isset($this->region) && $this->region !== null && $this->region !== '' && self::isBlankString($stream->region ?? null)) {
            $updates['region'] = $this->region;
        }

        if ($updates !== []) {
            $stream->update($updates);
        }
    }

    private static function isBlankString(mixed $value): bool
    {
        if ($value === null) {
            return true;
        }

        return trim((string) $value) === '';
    }

    private static function videoTitleLooksLikePlaceholder(string $title): bool
    {
        $t = trim(mb_strtolower($title));

        return $t === '' || in_array($t, [
            'bitchute video',
            'vimeo video',
            'untitled',
        ], true);
    }

    private static function videoTagsAreEmpty(Video $video): bool
    {
        $raw = $video->getRawOriginal('tags') ?? $video->tags;
        if ($raw === null || $raw === '') {
            return true;
        }
        if (is_string($raw)) {
            $dec = json_decode($raw, true);

            return ! is_array($dec) || $dec === [];
        }
        if (is_array($raw)) {
            return $raw === [];
        }

        return true;
    }

    private static function truncateStreamDescription(string $desc): string
    {
        return mb_substr($desc, 0, 500);
    }

    /**
     * @param  array<string|int, mixed>|null  $tags
     */
    private static function truncateStreamTagsJson(?array $tags): ?string
    {
        if ($tags === null || $tags === []) {
            return null;
        }
        $enc = json_encode($tags);
        if ($enc === false) {
            return null;
        }

        return strlen($enc) > 1200 ? substr($enc, 0, 1200) : $enc;
    }

    public function savePodcast(int $creator_id): Podcast
    {
        $appleId = (string) $this->id;
        $baseSlug = Str::slug($this->name);
        if ($baseSlug === '') {
            $baseSlug = 'podcast';
        }
        $slugCandidate = $baseSlug.'-'.$appleId;

        $incomingRss = trim((string) ($this->rss_url ?? ''));
        $fallbackRss = 'https://podcasts.apple.com/podcast/id'.$appleId;

        $podcast = Podcast::firstOrNew(['apple_podcast_id' => $appleId]);
        if (! $podcast->exists) {
            $slug = $slugCandidate;
            $n = 0;
            while (Podcast::where('slug', $slug)->exists()) {
                $n++;
                $slug = $slugCandidate.'-'.$n;
            }
            $podcast->slug = $slug;
        }
        $podcast->creator_id = $creator_id;

        $rssToStore = $incomingRss !== '' ? $incomingRss : $fallbackRss;
        if (
            $rssToStore !== ''
            && str_starts_with($rssToStore, 'http')
            && (! str_contains($rssToStore, 'podcasts.apple.com/podcast/id')
                || ! $podcast->exists
                || empty($podcast->rss_url))
        ) {
            $podcast->rss_url = $rssToStore;
        }
        if (empty($podcast->rss_url)) {
            $podcast->rss_url = $fallbackRss;
        }
        $podcast->title = $this->name;
        $podcast->description = $this->description ?? null;
        $podcast->thumbnail_url = $this->thumbnail_url ?: $podcast->thumbnail_url;
        $podcast->visibility = 'public';
        $podcast->save();

        return $podcast;
    }

    public static function convertFromStdClass($content)
    {
        $content_dto = new self(Platform::fromValue($content->platform), Kind::fromValue($content->kind), $content->id);
        $content_dto->name = $content->name;
        if (isset($content->thumbnail_url)) {
            $content_dto->thumbnail_url = $content->thumbnail_url;
        }
        $content_dto->description = property_exists($content, 'description') ? $content->description : null;
        if (isset($content->duration)) {
            $content_dto->duration = $content->duration;
        }
        if (isset($content->upload_time)) {
            $content_dto->upload_time = Carbon::parse($content->upload_time);
        }
        if (isset($content->publish_time)) {
            $content_dto->publish_time = Carbon::parse($content->publish_time);
        }
        if (isset($content->region)) {
            $content_dto->region = $content->region;
        }
        if (isset($content->language)) {
            $content_dto->language = $content->language;
        }
        if (isset($content->tags)) {
            $content_dto->tags = $content->tags;
        }
        if (isset($content->views)) {
            $content_dto->views = $content->views;
        }
        if (isset($content->likes)) {
            $content_dto->likes = $content->likes;
        }
        if (isset($content->dislikes)) {
            $content_dto->dislikes = $content->dislikes;
        }
        if (isset($content->assignable)) {
            $content_dto->assignable = $content->assignable;
        }
        if (isset($content->twitch_login)) {
            $content_dto->twitch_login = $content->twitch_login;
        }
        if (isset($content->audience)) {
            $content_dto->audience = Audience::fromValue($content->audience);
        }
        if (isset($content->category_slug)) {
            $content_dto->category_slug = $content->category_slug;
        }
        if (isset($content->creator_id)) {
            $content_dto->creator_id = $content->creator_id;
        }
        if (isset($content->podcast_episodes)) {
            $content_dto->podcast_episodes = $content->podcast_episodes;
        }
        if (isset($content->audio_url)) {
            $content_dto->audio_url = $content->audio_url;
        }
        if (isset($content->rss_url)) {
            $content_dto->rss_url = $content->rss_url;
        }
        if (isset($content->guid)) {
            $content_dto->guid = $content->guid;
        }
        if (isset($content->subcategory_name)) {
            $content_dto->subcategory_name = $content->subcategory_name;
        }
        if (isset($content->audio_file_type)) {
            $content_dto->audio_file_type = $content->audio_file_type;
        }
        if (isset($content->result_index)) {
            $content_dto->result_index = $content->result_index;
        }

        return $content_dto;
    }
}
