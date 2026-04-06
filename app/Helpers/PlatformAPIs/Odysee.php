<?php

namespace App\Helpers\PlatformAPIs;

use App\Enums\Audience;
use App\Enums\Kind;
use App\Enums\Platform;
use App\Helpers\ContentDTO;
use App\Helpers\CreatorDTO;
use App\Helpers\FirecrawlClient;
use App\Helpers\PlatformAPIs\PlatformInterfaces\iIsPlatform;
use App\Helpers\PlatformAPIs\PlatformInterfaces\iSearchable;
use App\Helpers\ResultDTO;
use App\Helpers\SearchQueryDTO;
use App\Helpers\Tools;
use App\Helpers\VideoDurationParser;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class Odysee implements iIsPlatform, iSearchable
{
    private const LIGHTHOUSE_PRIMARY = 'https://lighthouse.odysee.tv/search';

    private const LIGHTHOUSE_FALLBACK = 'https://lighthouse.lbry.com/search';

    private const FALLBACK_THUMB = 'https://odysee.com/apple-touch-icon.png';

    public static function getPlatform(): Platform
    {
        return Platform::Odysee;
    }

    public static function search(SearchQueryDTO $searchQuery): array
    {
        $size = min(20, max(1, $searchQuery->max_results));
        $params = [
            's' => $searchQuery->query,
            'size' => $size,
            'from' => 0,
            'include' => 'channel,channel_claim_id,thumbnail_url,title,description,duration,release_time',
            'mediaType' => 'video',
        ];

        $items = self::fetchLighthouse($params);
        if ($items === []) {
            return [];
        }

        $out = [];
        /** @var array<string, string> $channelClaimIdByHandleLc lowercase channel handle => 40-char hex claim id */
        $channelClaimIdByHandleLc = [];
        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }
            $dto = self::lighthouseHitToVideoResult($item);
            if ($dto !== null) {
                $out[] = $dto;
                self::indexChannelClaimFromLighthouseItem($item, $channelClaimIdByHandleLc);
            }
        }

        self::enrichOdyseeChannelAvatarsViaResolveApi($out, $channelClaimIdByHandleLc);
        self::enrichOdyseeChannelAvatarsViaFirecrawl($out);

        return Tools::validateDTOs(array_slice($out, 0, $size));
    }

    /**
     * @param  array<string, string>  $channelClaimIdByHandleLc
     * @param  list<ResultDTO>  $results
     */
    private static function enrichOdyseeChannelAvatarsViaResolveApi(array &$results, array $channelClaimIdByHandleLc): void
    {
        if ($channelClaimIdByHandleLc === []) {
            return;
        }

        $proxy = (string) config('services.odysee.proxy_url', '');
        if ($proxy === '' || ! str_starts_with($proxy, 'http')) {
            return;
        }

        $neededLc = [];
        foreach ($results as $dto) {
            if (! $dto instanceof ResultDTO || ! ($dto->creator instanceof CreatorDTO)) {
                continue;
            }
            $lc = strtolower(trim(ltrim($dto->creator->id, '@')));
            if ($lc === '' || trim((string) ($dto->creator->avatar_url ?? '')) !== '') {
                continue;
            }
            if (isset($channelClaimIdByHandleLc[$lc])) {
                $neededLc[$lc] = true;
            }
        }

        $handles = array_keys($neededLc);
        if ($handles === []) {
            return;
        }

        $avatarByLc = [];
        foreach (array_chunk($handles, 8) as $chunk) {
            $urls = [];
            foreach ($chunk as $lc) {
                $claimId = $channelClaimIdByHandleLc[$lc] ?? null;
                if (! is_string($claimId) || $claimId === '') {
                    continue;
                }
                $urls[] = 'lbry://@'.$lc.'#'.$claimId;
            }
            if ($urls === []) {
                continue;
            }
            $batch = self::fetchOdyseeResolveThumbnails($proxy, $urls);
            foreach ($batch as $lcKey => $imgUrl) {
                $avatarByLc[$lcKey] = $imgUrl;
            }
        }

        if ($avatarByLc === []) {
            return;
        }

        foreach ($results as $dto) {
            if (! $dto instanceof ResultDTO || ! ($dto->creator instanceof CreatorDTO)) {
                continue;
            }
            $lc = strtolower(trim(ltrim($dto->creator->id, '@')));
            if ($lc !== '' && isset($avatarByLc[$lc]) && trim((string) ($dto->creator->avatar_url ?? '')) === '') {
                $dto->creator->avatar_url = $avatarByLc[$lc];
            }
        }
    }

    /**
     * @param  list<string>  $urls
     * @return array<string, string> lowercase handle => https thumbnail url
     */
    private static function fetchOdyseeResolveThumbnails(string $proxyUrl, array $urls): array
    {
        try {
            $response = Http::timeout(30)
                ->acceptJson()
                ->asJson()
                ->post($proxyUrl, [
                    'jsonrpc' => '2.0',
                    'method' => 'resolve',
                    'params' => ['urls' => array_values($urls)],
                    'id' => 1,
                ]);
        } catch (Throwable $e) {
            Log::warning('Odysee resolve API transport error: '.$e->getMessage());

            return [];
        }

        if (! $response->successful()) {
            Log::warning('Odysee resolve API HTTP error', ['status' => $response->status()]);

            return [];
        }

        $json = $response->json();
        $result = $json['result'] ?? null;
        if (! is_array($result)) {
            return [];
        }

        $out = [];
        foreach ($result as $lbryUrl => $row) {
            if (! is_string($lbryUrl) || ! is_array($row)) {
                continue;
            }
            if (! preg_match('~lbry://@([^#]+)#([a-f0-9]{40})~i', $lbryUrl, $m)) {
                continue;
            }
            $lc = strtolower($m[1]);
            $value = $row['value'] ?? null;
            if (! is_array($value)) {
                continue;
            }
            $thumb = $value['thumbnail']['url'] ?? null;
            if (! is_string($thumb) || trim($thumb) === '') {
                continue;
            }
            $out[$lc] = trim($thumb);
        }

        return $out;
    }

    /**
     * @param  array<string, mixed>  $item
     * @param  array<string, string>  $channelClaimIdByHandleLc
     */
    private static function indexChannelClaimFromLighthouseItem(array $item, array &$channelClaimIdByHandleLc): void
    {
        $name = $item['name'] ?? null;
        $channelDisplay = is_string($item['channel'] ?? null) ? $item['channel'] : '';
        if (! is_string($name) || $name === '') {
            return;
        }

        $channelId = self::canonicalChannelId($name, $channelDisplay);
        if ($channelId === '') {
            return;
        }

        $claimRaw = $item['channel_claim_id'] ?? null;
        if (! is_string($claimRaw)) {
            return;
        }
        $claimId = strtolower(trim($claimRaw));
        if ($claimId === '' || ! preg_match('/^[a-f0-9]{40}$/', $claimId)) {
            return;
        }

        $channelClaimIdByHandleLc[strtolower($channelId)] = $claimId;
    }

    /**
     * Lighthouse search does not include channel profile images; optionally scrape channel pages when Firecrawl is configured.
     *
     * @param  list<ResultDTO>  $results
     */
    private static function enrichOdyseeChannelAvatarsViaFirecrawl(array &$results): void
    {
        $fc = FirecrawlClient::make();
        if ($fc === null) {
            return;
        }

        $needed = [];
        foreach ($results as $dto) {
            if (! $dto instanceof ResultDTO || ! ($dto->creator instanceof CreatorDTO)) {
                continue;
            }
            $cid = strtolower(trim(ltrim($dto->creator->id, '@')));
            if ($cid === '') {
                continue;
            }
            if (trim((string) ($dto->creator->avatar_url ?? '')) !== '') {
                continue;
            }
            $needed[$cid] = true;
        }

        $channelIds = array_slice(array_keys($needed), 0, 10);
        $avatarByChannel = [];
        foreach ($channelIds as $cid) {
            $page = $fc->scrape('https://odysee.com/@'.rawurlencode($cid), ['metadata'], 60);
            if ($page === null) {
                continue;
            }
            $meta = is_array($page['metadata'] ?? null) ? $page['metadata'] : [];
            $img = $meta['ogImage'] ?? null;
            if (is_string($img) && trim($img) !== '') {
                $avatarByChannel[$cid] = trim($img);
            }
        }

        if ($avatarByChannel === []) {
            return;
        }

        foreach ($results as $dto) {
            if (! $dto instanceof ResultDTO || ! ($dto->creator instanceof CreatorDTO)) {
                continue;
            }
            $cid = strtolower(trim(ltrim($dto->creator->id, '@')));
            if ($cid !== '' && isset($avatarByChannel[$cid]) && trim((string) ($dto->creator->avatar_url ?? '')) === '') {
                $dto->creator->avatar_url = $avatarByChannel[$cid];
            }
        }
    }

    /**
     * @param  array<string, mixed>  $params
     * @return list<array<string, mixed>>
     */
    private static function fetchLighthouse(array $params): array
    {
        foreach ([self::LIGHTHOUSE_PRIMARY, self::LIGHTHOUSE_FALLBACK] as $base) {
            try {
                $response = Http::timeout(20)
                    ->acceptJson()
                    ->get($base, $params);

                if (! $response->successful()) {
                    Log::warning('Odysee Lighthouse HTTP error', [
                        'url' => $base,
                        'status' => $response->status(),
                    ]);

                    continue;
                }

                $data = $response->json();
                if (is_array($data)) {
                    return $data;
                }
            } catch (Throwable $e) {
                Log::warning('Odysee Lighthouse request failed: '.$e->getMessage(), ['url' => $base]);
            }
        }

        return [];
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private static function lighthouseHitToVideoResult(array $item): ?ResultDTO
    {
        $name = $item['name'] ?? null;
        $claimId = $item['claimId'] ?? null;
        if (! is_string($name) || $name === '' || ! is_string($claimId) || $claimId === '') {
            return null;
        }

        $embedId = $name.':'.$claimId;
        $title = is_string($item['title'] ?? null) ? $item['title'] : '';
        if ($title === '') {
            return null;
        }

        $channelDisplay = is_string($item['channel'] ?? null) ? $item['channel'] : '';
        $channelId = self::canonicalChannelId($name, $channelDisplay);
        if ($channelId === '') {
            return null;
        }

        $description = is_string($item['description'] ?? null) ? $item['description'] : '';
        $thumbRaw = $item['thumbnail_url'] ?? null;
        $thumbnailUrl = self::normalizeThumbnailUrl(is_string($thumbRaw) ? $thumbRaw : '');

        $durationSec = VideoDurationParser::secondsFromScraperRow($item);
        if ($durationSec <= 0) {
            $durationSec = VideoDurationParser::secondsFromMixed($item['duration'] ?? null) ?? 0;
        }
        $duration = (string) max(0, $durationSec);

        $release = $item['release_time'] ?? null;
        $publishTime = null;
        if (is_string($release) && $release !== '') {
            try {
                $publishTime = Carbon::parse($release);
            } catch (Throwable) {
                $publishTime = null;
            }
        }
        if ($publishTime === null) {
            $publishTime = Carbon::now();
        }

        $resultDTO = new ResultDTO(Platform::Odysee, Kind::Video);
        $resultDTO->platform = Platform::Odysee;

        $creatorDTO = new CreatorDTO(Platform::Odysee, $channelId);
        $displayName = $channelDisplay !== '' ? ltrim(trim($channelDisplay), '@') : $channelId;
        $creatorDTO->name = $displayName !== '' ? $displayName : $channelId;
        $creatorDTO->description = null;
        $creatorDTO->avatar_url = null;
        $creatorDTO->banner_url = null;

        $contentDTO = new ContentDTO(Platform::Odysee, Kind::Video, $embedId);
        $contentDTO->kind = Kind::Video;
        $contentDTO->name = $title;
        $contentDTO->description = $description !== '' ? $description : null;
        $contentDTO->thumbnail_url = $thumbnailUrl;
        $contentDTO->publish_time = $publishTime;
        $contentDTO->duration = $duration;
        $contentDTO->creator_id = $channelId;
        $contentDTO->tags = [];
        $contentDTO->audience = Audience::ALL;
        $contentDTO->storage_slug = Platform::Odysee->getPrefix().'_'.$claimId;

        $resultDTO->creator = $creatorDTO;
        $resultDTO->content = $contentDTO;

        return $resultDTO;
    }

    private static function canonicalChannelId(string $claimName, string $channelDisplay): string
    {
        $claimName = trim($claimName);
        if (preg_match('/^@([^\/]+)\//u', $claimName, $m)) {
            return ltrim($m[1], '@');
        }

        $fallback = trim($channelDisplay);
        $fallback = ltrim($fallback, '@');

        return $fallback;
    }

    private static function normalizeThumbnailUrl(string $raw): string
    {
        $raw = trim($raw);
        if ($raw === '') {
            return self::FALLBACK_THUMB;
        }
        if (str_starts_with($raw, 'http://') || str_starts_with($raw, 'https://')) {
            return $raw;
        }

        return 'https://thumbnails.odycdn.com/optimize/s:390:0/quality:85/plain/'.$raw;
    }
}
