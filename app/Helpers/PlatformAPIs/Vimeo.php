<?php

namespace App\Helpers\PlatformAPIs;

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
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Throwable;
use Vimeo\Vimeo as VimeoSDK;

class Vimeo implements iSearchable, iIsPlatform
{
    public VimeoSDK $client;

    public function __construct()
    {
        $this->client = new VimeoSDK(config('platforms.vimeo.client_id'), config('platforms.vimeo.client_secret'));
    }

    public static function getPlatform(): Platform
    {
        return Platform::Vimeo;
    }

    public static function getCreator(string $id)
    {
        $fc = FirecrawlClient::make();
        if ($fc !== null) {
            $fromFirecrawl = self::getCreatorViaFirecrawl($fc, $id);
            if ($fromFirecrawl !== null) {
                return $fromFirecrawl;
            }
        }

        try {
            $vimeo = new self;
            $fields = implode(',', [
                'uri',
                'body',
                'name',
                'pictures',
                'bio',
                'location_details',
            ]);
            $bundle = $vimeo->client->request('/users/'.$id.'?fields='.$fields);
            $data = $bundle['body'] ?? [];
            if (($data['error'] ?? null) !== null) {
                Log::info('Vimeo getCreator API error, using minimal DTO', ['id' => $id, 'error' => $data]);

                return self::minimalCreatorDto($id);
            }

            $creatorDTO = new CreatorDTO(Platform::Vimeo, $id);
            $creatorDTO->id = str_replace('/users/', '', $data['uri'] ?? '/users/'.$id);
            $creatorDTO->name = $data['name'] ?? $id;
            $creatorDTO->description = $data['bio'] ?? '';
            $sizes = $data['pictures']['sizes'] ?? [];
            $creatorDTO->avatar_url = $sizes !== [] ? (string) (end($sizes)['link'] ?? '') : '';
            $creatorDTO->region = $data['location_details']['country_iso_code'] ?? null;

            return $creatorDTO;
        } catch (Throwable $e) {
            Log::warning('Vimeo getCreator failed: '.$e->getMessage());

            return self::minimalCreatorDto($id);
        }
    }

    public static function search(SearchQueryDTO $searchQueryDTO)
    {
        $fc = FirecrawlClient::make();
        if ($fc !== null) {
            $fromFc = self::searchViaFirecrawl($fc, $searchQueryDTO);
            if ($fromFc !== []) {
                return $fromFc;
            }
        }

        $vimeoClientId = (string) config('platforms.vimeo.client_id');
        if ($vimeoClientId === '') {
            return [];
        }

        try {
            $response = (new Vimeo)->client->request('/videos', [
                'query' => $searchQueryDTO->query,
                'per_page' => ($searchQueryDTO->max_results <= 100) ? $searchQueryDTO->max_results : 100,
                'fields' => 'uri,name,description,duration,release_time,pictures,tags,user',
            ]);
            $body = $response['body'] ?? [];
            $rows = $body['data'] ?? null;
            if (! is_array($rows)) {
                Log::info('Vimeo search API returned no data', ['body' => $body]);

                return [];
            }

            return self::extractedContentAndUserDtoMap($rows);
        } catch (Throwable $e) {
            Log::warning('Vimeo search failed: '.$e->getMessage());

            return [];
        }
    }

    public static function getCreatorVideos(string $id, ?int $page = null, $maxResults = 100): array // SearchResultDTO
    {
        if ($maxResults > 100) {
            throw new \Exception('Max results cannot be greater than 100');
        }

        $fc = FirecrawlClient::make();
        if ($fc !== null) {
            $fromFc = self::getCreatorVideosViaFirecrawl($fc, $id, $page ?? 1, (int) $maxResults);
            if ($fromFc['results'] !== []) {
                return $fromFc;
            }
        }

        $page = $page ?? 1;
        try {
            $api = new Vimeo;
            $response = $api->client->request('/users/'.$id.'/videos', [
                'sort' => 'date',
                'per_page' => $maxResults,
                'page' => $page,
                'fields' => 'uri,name,description,duration,release_time,pictures,tags,user',
            ])['body'];

            if (! isset($response['data'])) {
                return [
                    'next' => $page + 1,
                    'hasNext' => false,
                    'results' => [],
                ];
            }

            $results = self::returnContentDTOarrayMap($response['data']);

            return [
                'next' => $page + 1,
                'hasNext' => boolval($response['paging']['next'] ?? false),
                'results' => $results,
            ];
        } catch (Throwable $e) {
            Log::warning('Vimeo getCreatorVideos failed: '.$e->getMessage());

            return [
                'next' => ($page ?? 1) + 1,
                'hasNext' => false,
                'results' => [],
            ];
        }
    }

    public static function getFeaturedVideos(int $maxResults = 10): array
    {
        $fc = FirecrawlClient::make();
        if ($fc !== null) {
            $hits = $fc->search('site:vimeo.com/channels/staffpicks', min(15, $maxResults), []);
            $videoIds = [];
            foreach ($hits as $hit) {
                $vid = self::videoIdFromUrl((string) ($hit['url'] ?? ''));
                if ($vid !== null) {
                    $videoIds[$vid] = true;
                }
            }
            if ($videoIds !== []) {
                return self::buildResultsFromVideoIdsFirecrawl($fc, array_keys($videoIds), $maxResults);
            }

            $hits = $fc->search('site:vimeo.com creative video', min(15, $maxResults), []);
            $ids = [];
            foreach ($hits as $hit) {
                $vid = self::videoIdFromUrl((string) ($hit['url'] ?? ''));
                if ($vid !== null) {
                    $ids[$vid] = true;
                }
            }
            if ($ids !== []) {
                return self::buildResultsFromVideoIdsFirecrawl($fc, array_keys($ids), $maxResults);
            }
        }

        try {
            $api = new Vimeo;
            $response = $api->client->request('/videos', [
                'filter' => 'trending',
                'sort' => 'date',
                'direction' => 'desc',
                'per_page' => $maxResults,
                'fields' => 'uri,name,description,duration,release_time,pictures,tags,user',
            ]);
            $body = $response['body'] ?? [];
            $data = $body['data'] ?? [];

            return is_array($data) ? self::extractedContentAndUserDtoMap($data) : [];
        } catch (Throwable $e) {
            Log::warning('Vimeo getFeaturedVideos failed: '.$e->getMessage());

            return [];
        }
    }

    /**
     * @param  ContentDTO[]  $data
     * @return ContentDTO[]
     */
    public static function returnContentDTOarrayMap($data): array
    {
        if (! is_array($data) || $data === []) {
            return [];
        }

        return Tools::validateDTOs(array_map(function ($value) {
            $contentDTO = new ContentDTO(Platform::Vimeo, Kind::Video, str_replace('/videos/', '', $value['uri']));

            $contentDTO->kind = Kind::Video;
            $contentDTO->name = $value['name'];
            $contentDTO->duration = (string) $value['duration'];
            $contentDTO->publish_time = Carbon::make($value['release_time']);
            $contentDTO->thumbnail_url = $value['pictures']['base_link'];
            $contentDTO->creator_id = str_replace('/users/', '', $value['user']['uri']);
            $contentDTO->tags = array_map(fn ($item) => $item['name'], $value['tags']);

            return $contentDTO;
        }, $data));
    }

    /**
     * @return array<int, ResultDTO>
     */
    public static function extractedContentAndUserDtoMap($data): array
    {
        if (! is_array($data) || $data === []) {
            return [];
        }

        return Tools::validateDTOs(Arr::map($data, function ($value) {
            $resultDTO = new ResultDTO(Platform::Vimeo, Kind::Video);
            $contentDTO = new ContentDTO(Platform::Vimeo, Kind::Video,
                str_replace('/videos/', '', $value['uri'])
            );
            $creatorDTO = new CreatorDTO(Platform::Vimeo,
                str_replace('/users/', '', $value['user']['uri'])
            );
            $resultDTO->platform = Platform::Vimeo;

            $contentDTO->kind = Kind::Video;
            $contentDTO->publish_time = Carbon::parse($value['release_time']);
            $contentDTO->name = $value['name'];
            $contentDTO->duration = (string) $value['duration'];
            $contentDTO->thumbnail_url = $value['pictures']['base_link'];
            $value['tags'] = array_filter($value['tags'] ?? [], function ($item) {
                return $item['name'] ?? false;
            });
            $contentDTO->tags = array_map(fn ($item) => $item['name'], $value['tags']);
            $contentDTO->description = $value['description'];
            $contentDTO->creator_id = str_replace('/users/', '', $value['user']['uri']);

            $creatorDTO->name = $value['user']['name'];
            $creatorDTO->description = $value['user']['bio'] ?? '';
            $sizes = $value['user']['pictures']['sizes'] ?? [];
            $creatorDTO->avatar_url = $sizes !== [] ? (string) (end($sizes)['link'] ?? '') : '';

            $resultDTO->content = $contentDTO;
            $resultDTO->creator = $creatorDTO;

            return $resultDTO;
        }));
    }

    private static function minimalCreatorDto(string $id): CreatorDTO
    {
        $c = new CreatorDTO(Platform::Vimeo, $id);
        $c->name = $id;

        return $c;
    }

    private static function getCreatorViaFirecrawl(FirecrawlClient $fc, string $id): ?CreatorDTO
    {
        $candidates = array_unique([
            'https://vimeo.com/'.rawurlencode($id),
            'https://vimeo.com/user'.preg_replace('/\D/', '', $id),
        ]);

        foreach ($candidates as $url) {
            $data = $fc->scrape($url, ['markdown', 'links']);
            if ($data === null) {
                continue;
            }
            $meta = is_array($data['metadata'] ?? null) ? $data['metadata'] : [];
            $title = self::metaTitle($meta);
            $md = (string) ($data['markdown'] ?? '');

            $name = $title;
            if ($name === '' && preg_match('/^\#\s+(.+)$/m', $md, $m)) {
                $name = trim($m[1]);
            }

            $avatar = is_string($meta['ogImage'] ?? null) ? $meta['ogImage'] : null;
            if ($avatar === null && preg_match('/!\[[^\]]*\]\((https:\/\/[^)]+\.(?:jpg|jpeg|png|webp)[^)]*)\)/i', $md, $m)) {
                $avatar = $m[1];
            }

            $description = is_string($meta['description'] ?? null) ? $meta['description'] : '';
            if ($description === '' && preg_match('/Share\s*\n*(.+)/is', $md, $m)) {
                $description = trim($m[1]);
            }

            if ($name === '' && $description === '' && $avatar === null) {
                continue;
            }

            $creatorDTO = new CreatorDTO(Platform::Vimeo, $id);
            $creatorDTO->name = $name !== '' ? $name : $id;
            $creatorDTO->description = $description;
            $creatorDTO->avatar_url = (string) $avatar;

            return $creatorDTO;
        }

        return null;
    }

    /**
     * @return list<ResultDTO>
     */
    private static function searchViaFirecrawl(FirecrawlClient $fc, SearchQueryDTO $searchQueryDTO): array
    {
        $query = trim($searchQueryDTO->query);
        if ($query === '') {
            return [];
        }

        $limit = min(20, max(3, $searchQueryDTO->max_results));
        $hits = $fc->search('site:vimeo.com '.$query, $limit, []);

        $out = [];
        $seen = [];
        foreach ($hits as $hit) {
            $url = (string) ($hit['url'] ?? '');
            $vid = self::videoIdFromUrl($url);
            if ($vid === null || isset($seen[$vid])) {
                continue;
            }
            $seen[$vid] = true;

            $resultDTO = new ResultDTO(Platform::Vimeo, Kind::Video);
            $contentDTO = new ContentDTO(Platform::Vimeo, Kind::Video, $vid);
            $contentDTO->name = (string) ($hit['title'] ?? 'Vimeo video');
            $contentDTO->description = (string) ($hit['description'] ?? '');
            $contentDTO->thumbnail_url = '';
            $contentDTO->publish_time = Carbon::now();
            $contentDTO->duration = '0';
            $contentDTO->creator_id = 'vimeo';

            $creatorDTO = new CreatorDTO(Platform::Vimeo, 'vimeo');
            $creatorDTO->name = 'Vimeo';

            $resultDTO->content = $contentDTO;
            $resultDTO->creator = $creatorDTO;

            $out[] = $resultDTO;
        }

        if ($out !== []) {
            self::enrichTopFirecrawlResults($fc, $out, 5);
        }

        return Tools::validateDTOs($out);
    }

    /**
     * @param  list<ResultDTO>  $results
     */
    private static function enrichTopFirecrawlResults(FirecrawlClient $fc, array &$results, int $max): void
    {
        foreach (array_slice($results, 0, $max) as $resultDTO) {
            $content = $resultDTO->content;
            if (! $content instanceof ContentDTO) {
                continue;
            }
            $vid = $content->id;
            $page = $fc->scrape('https://vimeo.com/'.$vid, ['markdown']);
            if ($page === null) {
                continue;
            }
            $meta = is_array($page['metadata'] ?? null) ? $page['metadata'] : [];
            $title = self::metaTitle($meta);
            if ($title !== '') {
                $content->name = $title;
            }
            if (is_string($meta['ogImage'] ?? null)) {
                $content->thumbnail_url = $meta['ogImage'];
            }
            $md = (string) ($page['markdown'] ?? '');
            if (preg_match('/(?:Duration|duration):\s*([^\n]+)/', $md, $m)) {
                $sec = self::parseLooseDuration($m[1]);
                if ($sec !== null) {
                    $content->duration = (string) $sec;
                }
            }
            if (preg_match('/By\s+\[([^\]]+)\]\((https:\/\/vimeo\.com\/[^)]+)\)/i', $md, $mm)) {
                $slug = basename(parse_url($mm[2], PHP_URL_PATH) ?: 'vimeo');
                $resultDTO->creator = new CreatorDTO(Platform::Vimeo, $slug !== '' ? $slug : 'vimeo');
                $resultDTO->creator->name = trim($mm[1]);
                $content->creator_id = $resultDTO->creator->id;
            }
        }
    }

    /**
     * @return array{next: int, hasNext: bool, results: array<int, ResultDTO>}
     */
    private static function getCreatorVideosViaFirecrawl(FirecrawlClient $fc, string $id, int $page, int $maxResults): array
    {
        $urls = [
            'https://vimeo.com/'.rawurlencode($id).'/videos',
            'https://vimeo.com/user'.preg_replace('/\D/', '', $id).'/videos',
        ];

        foreach ($urls as $listUrl) {
            $data = $fc->scrape($listUrl, ['markdown', 'links']);
            if ($data === null) {
                continue;
            }

            $links = [];
            if (isset($data['links']) && is_array($data['links'])) {
                $links = $data['links'];
            }
            $md = (string) ($data['markdown'] ?? '');
            preg_match_all('#https://vimeo\.com/(?:video/)?(\d{6,})#i', $md, $m);
            foreach ($m[1] ?? [] as $vid) {
                $links[] = 'https://vimeo.com/'.$vid;
            }

            $videoIds = [];
            foreach ($links as $link) {
                $vid = self::videoIdFromUrl((string) $link);
                if ($vid !== null) {
                    $videoIds[$vid] = true;
                }
            }

            if ($videoIds === []) {
                continue;
            }

            $ids = array_slice(array_keys($videoIds), 0, $maxResults);
            $bundled = self::buildResultsFromVideoIdsFirecrawl($fc, $ids, $maxResults);
            $results = [];
            foreach ($bundled as $resultDTO) {
                if ($resultDTO->content instanceof ContentDTO) {
                    $results[] = $resultDTO->content;
                }
            }

            return [
                'next' => $page + 1,
                'hasNext' => false,
                'results' => $results,
            ];
        }

        return [
            'next' => $page + 1,
            'hasNext' => false,
            'results' => [],
        ];
    }

    /**
     * @param  list<string>  $videoIds
     * @return list<ResultDTO>
     */
    private static function buildResultsFromVideoIdsFirecrawl(FirecrawlClient $fc, array $videoIds, int $cap): array
    {
        $out = [];
        foreach (array_slice($videoIds, 0, $cap) as $vid) {
            $page = $fc->scrape('https://vimeo.com/'.$vid, ['markdown']);
            if ($page === null) {
                continue;
            }
            $meta = is_array($page['metadata'] ?? null) ? $page['metadata'] : [];
            $title = self::metaTitle($meta) ?: 'Vimeo video';
            $md = (string) ($page['markdown'] ?? '');

            $resultDTO = new ResultDTO(Platform::Vimeo, Kind::Video);
            $contentDTO = new ContentDTO(Platform::Vimeo, Kind::Video, $vid);
            $contentDTO->name = $title;
            $contentDTO->description = is_string($meta['description'] ?? null) ? $meta['description'] : '';
            $contentDTO->thumbnail_url = is_string($meta['ogImage'] ?? null) ? $meta['ogImage'] : '';
            $contentDTO->publish_time = Carbon::now();
            $contentDTO->duration = '0';
            if (preg_match('/(?:Duration|duration):\s*([^\n]+)/', $md, $m)) {
                $sec = self::parseLooseDuration($m[1]);
                if ($sec !== null) {
                    $contentDTO->duration = (string) $sec;
                }
            }

            $creatorDTO = new CreatorDTO(Platform::Vimeo, 'vimeo');
            $creatorDTO->name = 'Vimeo';
            if (preg_match('/By\s+\[([^\]]+)\]\((https:\/\/vimeo\.com\/[^)]+)\)/i', $md, $mm)) {
                $slug = basename(parse_url($mm[2], PHP_URL_PATH) ?: 'vimeo');
                $creatorDTO = new CreatorDTO(Platform::Vimeo, $slug !== '' ? $slug : 'vimeo');
                $creatorDTO->name = trim($mm[1]);
                $contentDTO->creator_id = $creatorDTO->id;
            }

            $resultDTO->content = $contentDTO;
            $resultDTO->creator = $creatorDTO;
            $out[] = $resultDTO;
        }

        return Tools::validateDTOs($out);
    }

    private static function videoIdFromUrl(string $url): ?string
    {
        if (preg_match('#vimeo\.com/(?:video/)?(\d{6,})#i', $url, $m)) {
            return $m[1];
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    private static function metaTitle(array $meta): string
    {
        $t = $meta['title'] ?? '';
        if (is_string($t)) {
            return $t;
        }
        if (is_array($t)) {
            return trim((string) ($t[0] ?? ''));
        }

        return '';
    }

    private static function parseLooseDuration(string $raw): ?int
    {
        $raw = trim($raw);
        if (preg_match('/^(\d+)\s*s(?:ec(?:onds?)?)?$/i', $raw, $m)) {
            return (int) $m[1];
        }
        if (preg_match('/^(\d+):(\d+)(?::(\d+))?$/', $raw, $m)) {
            if (isset($m[3])) {
                return (int) $m[1] * 3600 + (int) $m[2] * 60 + (int) $m[3];
            }

            return (int) $m[1] * 60 + (int) $m[2];
        }

        return null;
    }
}
