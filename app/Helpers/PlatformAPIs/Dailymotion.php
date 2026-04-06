<?php

namespace App\Helpers\PlatformAPIs;

use App\Enums\Kind;
use App\Enums\Platform;
use App\Helpers\ContentDTO;
use App\Helpers\CreatorDTO;
use App\Helpers\PlatformAPIs\PlatformInterfaces\iIsPlatform;
use App\Helpers\PlatformAPIs\PlatformInterfaces\iSearchable;
use App\Helpers\ResultDTO;
use App\Helpers\SearchQueryDTO;
use App\Helpers\Tools;
use App\Helpers\VideoDurationParser;
use Carbon\Carbon;
use Dailymotion as DailymotionSDK;
use Illuminate\Support\Arr;

class Dailymotion implements iIsPlatform, iSearchable
{
    public DailymotionSDK $client;

    /**
     * /videos list search only accepts claimer.* for uploader fields (owner.* was removed server-side).
     *
     * @see https://developers.dailymotion.com/reference/video-fields
     */
    private static array $searchFields = [
        'id',
        'item_type',
        'title',
        'thumbnail_720_url',
        'created_time',
        'duration',
        'views_total',
        'likes_total',
        'channel',
        'description',
        'channel.description',
        'channel.name',
        // Keep claimer fields minimal — some need extra OAuth scopes (e.g. fullname → userinfo).
        'claimer.id',
        'claimer.username',
        'claimer.screenname',
        'claimer.nickname',
    ];

    public function __construct()
    {
        $dailymotion_client = new DailymotionSDK;

        $dailymotion_client->setGrantType(
            DailymotionSDK::GRANT_TYPE_CLIENT_CREDENTIALS,
            config('platforms.dailymotion.client_key'),
            config('platforms.dailymotion.client_secret')
        );

        $this->client = $dailymotion_client;
    }

    public static function getPlatform(): Platform
    {
        return Platform::Dailymotion;
    }

    // max ids is 100
    public function getCreators(array $ids)
    {
        // validate ids
        if (count($ids) > 100) {
            throw new \Exception('Too many ids, max 100');
        }

        $response = $this->client->get('/users', [
            'fields' => [
                'id',
                'description',
                'cover_url',
                'screenname',
                'avatar_720_url',
                'avatar_480_url',
                'avatar_360_url',
                'avatar_240_url',
                'avatar_url',
                'country',
                'language',
            ],
            'ids' => implode(',', $ids),
            'limit' => 100,
        ]);

        return Arr::map($response['list'],
            function ($value) {
                $creatorDTO = new CreatorDTO(Platform::Dailymotion, $value['id']);
                $creatorDTO->description = $value['description'];
                $creatorDTO->name = $value['screenname'];
                $creatorDTO->avatar_url = self::pickAvatarFromUserApiRow($value);
                $creatorDTO->banner_url = $value['cover_url'];
                $creatorDTO->region = $value['country'];
                $creatorDTO->language = $value['language'];

                return $creatorDTO;
            });
    }

    public static function search(SearchQueryDTO $searchQuery)
    {

        $dm = new self;
        //        try {
        $response = $dm->client->get('/videos/',
            [
                'search' => $searchQuery->query,
                'fields' => self::$searchFields,
                'limit' => ($searchQuery->max_results <= 100) ? $searchQuery->max_results : 100,
                //                    'page' => $pageToken
            ]
        );

        //            dd($response);
        return Tools::validateDTOs(Arr::map($response['list'], function ($item) {
            $resultDTO = new ResultDTO(Platform::Dailymotion, Kind::Video);

            $claimerId = trim((string) ($item['claimer.id'] ?? ''));
            if ($claimerId === '') {
                $ch = $item['channel'] ?? null;
                $claimerId = is_string($ch) ? trim($ch) : '';
            }
            $claimerName = trim((string) ($item['claimer.screenname'] ?? $item['claimer.username'] ?? $item['claimer.nickname'] ?? ''));
            if ($claimerName === '') {
                $claimerName = trim((string) ($item['channel.name'] ?? ''));
            }
            if ($claimerName === '') {
                $claimerName = $claimerId !== '' ? $claimerId : 'Unknown';
            }

            $contentDTO = new ContentDTO(Platform::Dailymotion, Kind::Video, $item['id']);
            $contentDTO->name = $item['title'];
            $contentDTO->description = $item['description'];
            $contentDTO->thumbnail_url = $item['thumbnail_720_url'];
            $dmDur = VideoDurationParser::secondsFromMixed($item['duration'] ?? null)
                ?? VideoDurationParser::secondsFromScraperRow($item);
            $contentDTO->duration = (string) max(0, $dmDur);
            $contentDTO->views = $item['views_total'];
            $contentDTO->likes = $item['likes_total'];
            $contentDTO->publish_time = Carbon::parse($item['created_time']);

            $creatorDTO = new CreatorDTO(Platform::Dailymotion, $claimerId);
            $creatorDTO->name = $claimerName;
            $creatorDTO->description = null;
            $creatorDTO->region = null;
            $creatorDTO->language = null;
            $creatorDTO->avatar_url = self::pickClaimerAvatarUrl($item);
            $creatorDTO->banner_url = null;

            $contentDTO->creator_id = $claimerId !== '' ? $claimerId : 'unknown';

            //                $creatorDTO->avatar_url = $item['owner.avatar_720_url'];
            //                $creatorDTO->banner_url = $item['owner.cover_url'];

            //                DTO->description = $item['channel.description'];
            $resultDTO->content = $contentDTO;
            $resultDTO->creator = $creatorDTO;

            return $resultDTO;
        }));
        //            return [
        //                "pageTokenInfo" => self::getPageTokenInfo($response, $pageToken),
        //                "results" => self::convertResponseToDTOs($response['list'])
        //            ];
        //        }
        //        catch (\Exception $e){
        //            return [
        //                "pageTokenInfo" => null,
        //                "results" => []
        //            ];
        //        }
    }

    /**
     * @param  array<string, mixed>  $item  Flattened /videos list row.
     */
    private static function pickClaimerAvatarUrl(array $item): ?string
    {
        foreach ([
            'claimer.avatar_720_url',
            'claimer.avatar_480_url',
            'claimer.avatar_360_url',
            'claimer.avatar_240_url',
            'claimer.avatar_url',
        ] as $key) {
            $v = $item[$key] ?? null;
            if (is_string($v) && trim($v) !== '') {
                return trim($v);
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $value  User row from /users (non-flattened keys).
     */
    private static function pickAvatarFromUserApiRow(array $value): ?string
    {
        foreach ([
            'avatar_720_url',
            'avatar_480_url',
            'avatar_360_url',
            'avatar_240_url',
            'avatar_url',
        ] as $key) {
            $v = $value[$key] ?? null;
            if (is_string($v) && trim($v) !== '') {
                return trim($v);
            }
        }

        return null;
    }

    public static function getCreatorVideosBeforeDate(string $id, ?Carbon $date = null, $maxResults = 100): array
    {
        if ($maxResults > 100) {
            throw new \Exception('Max results cannot be greater than 100');
        }

        $api = new Dailymotion;
        $queryParams = [
            'fields' => self::$searchFields,
            'created_before' => $date?->timestamp,
            'limit' => $maxResults,
        ];
        $response = $api->client->get('/user/'.$id.'/videos', $queryParams);

        /** @var list<ContentDTO> $results */
        $results = Arr::map($response['list'], function ($value) use ($id) {
            $contentDTO = new ContentDTO(Platform::Dailymotion, Kind::Video, $value['id']);

            $contentDTO->name = $value['title'];
            $dmDur = VideoDurationParser::secondsFromMixed($value['duration'] ?? null)
                ?? VideoDurationParser::secondsFromScraperRow($value);
            $contentDTO->duration = (string) max(0, $dmDur);
            $contentDTO->publish_time = Carbon::createFromTimestamp($value['created_time']);
            $contentDTO->thumbnail_url = $value['thumbnail_720_url'];
            $contentDTO->views = $value['views_total'];
            $contentDTO->likes = $value['likes_total'] ?: 0;
            $contentDTO->creator_id = (string) $id;
            $contentDTO->description = $value['description'];

            return $contentDTO;
        });

        return [
            'next' => end($response['list'])['created_time'] ?? null, // timestamp
            'hasNext' => boolval($response['has_more']),
            'results' => $results,  // ContentDTO
        ];
    }
}
