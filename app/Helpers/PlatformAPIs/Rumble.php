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
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Rumble implements iSearchable, iIsPlatform
{


    public static function getPlatform(): Platform
    {
        return Platform::Rumble;
    }

    public static function getCreators(array $ids): array
    {
        $startUrls = array_map(fn($id) => "https://rumble.com/c/$id", $ids);
        $apify = new Apify('azzouzana~rumble-all-inclusive-scraper');

        try {
            $runId = $apify->runActor([
                'startUrls' => $startUrls,
                'scrapeChannelVideos' => false,
                'scrapeChannelPlaylists' => false,
            ]);

            $runData = $apify->waitForRunCompletion($runId);
            $datasetId = $runData['defaultDatasetId'];
            $data = $apify->getDatasetItems($datasetId);

            $resultDTOs = array_map(function ($value) {
                $resultDTO = new ResultDTO(Platform::Rumble, Kind::Creator);
                $creatorDTO = new CreatorDTO(Platform::Rumble, $value['slug']);
                $creatorDTO->name = $value['name'];
                $creatorDTO->description = $value['description'];
                $creatorDTO->avatar_url = $value['thumb'];
                $creatorDTO->banner_url = $value['backsplash'] ?? null;
                $resultDTO->creator = $creatorDTO;
                return $resultDTO;
            }, $data);

            return Tools::validateDTOs($resultDTOs);
        } catch (Exception $e) {
            Log::error('Error in Rumble getCreators: ' . $e->getMessage());
            return [];
        }
    }

    public static function search(SearchQueryDTO $searchQueryDTO): array
    {
        $apify = new Apify('azzouzana~rumble-all-inclusive-scraper');

        try {
            $runId = $apify->runActor([
                'startUrls' => ["https://rumble.com/search/all?q=" . urlencode($searchQueryDTO->query)],
            ]);

            $runData = $apify->waitForRunCompletion($runId);
            $datasetId = $runData['defaultDatasetId'];
            $items = $apify->getDatasetItems($datasetId);

            // Processing search results
            $channelsWanted = 3;
            // Filter items
            $items = array_filter($items, function ($item) use (&$channelsWanted) {
                if (isset($item['type']) && $item['type'] === 'user') {
                    return false;
                }
                if (isset($item['type']) && $item['type'] === 'channel') {
                    if ($channelsWanted > 0) {
                        $channelsWanted -= 1;
                        return true;
                    } else {
                        return false;
                    }
                }

                return true;
            });

            // Limit the results to 20
            $items = array_slice($items, 0, 20);

            return Tools::validateDTOs(Arr::map($items, function ($value) {
                if (!isset($value['object_type']) && !isset($value['type'])) {
                    return null;
                }

                $type = $value['object_type'] ?? $value['type'];

                switch ($type) {
                    case "video":
                        // Setup resultDTO
                        $resultDTO = new ResultDTO(Platform::Rumble, Kind::Video);
                        $resultDTO->platform = Platform::Rumble;

                        try {
                            $view = $value['log']['view'];
                            $videoId = explode('...', $view)[1];
                            $videoId = explode('.', $videoId)[0];
                            $videoId = 'v' . $videoId;
                        } catch (Exception $e) {
                            Log::info("Failed to get videoId from rumble search result: " . $e->getMessage());
                            return null;
                        }

                        $contentDTO = new ContentDTO(Platform::Rumble, Kind::Video, $videoId);

                        // Setup creatorDTO
                        $creator = $value['by'];
                        $id = explode('/', $creator['relative_url'])[2]; // AlexJonesTV
                        $creatorDTO = new CreatorDTO(Platform::Rumble, $id);

                        $creatorDTO->name = $value['by']['title'];
                        $creatorDTO->description = "";
                        $creatorDTO->avatar_url = $value['by']['thumb'];

                        $contentDTO->kind = Kind::Video;
                        $contentDTO->publish_time = Carbon::make($value['upload_date']);
                        $contentDTO->name = $value['title'];
                        $contentDTO->duration = $value['duration'];
                        $contentDTO->thumbnail_url = $value['thumb'];
                        $contentDTO->tags = $value['tags'] ?? [];
                        $contentDTO->description = "This video was uploaded by {$creator['name']} on Rumble.";
                        $contentDTO->creator_id = $id;

                        $resultDTO->content = $contentDTO;
                        $resultDTO->creator = $creatorDTO;
                        return $resultDTO;
                    case "channel":
                        $resultDTO = new ResultDTO(Platform::Rumble, Kind::Creator);
                        $resultDTO->platform = Platform::Rumble;

                        $creatorDTO = new CreatorDTO(Platform::Rumble, $value['slug']);
                        $creatorDTO->kind = Kind::Creator;
                        $creatorDTO->name = $value['name'];
                        $creatorDTO->description = $value['description'];
                        $creatorDTO->avatar_url = $value['thumb'];
                        $creatorDTO->banner_url = $value['backsplash'] ?? null;

                        $resultDTO->creator = $creatorDTO;
                        return $resultDTO;
                    default:
                        Log::info("Rumble search result type not supported: $type");
                }

                return null;
            }));
        } catch (Exception $e) {
            Log::error('Error in Rumble search: ' . $e->getMessage());
            return [];
        }
    }

    public static function getCreatorVideos(string $id, int $page = null, $maxResults = 100): array
    {
        $apify = new Apify('azzouzana~rumble-all-inclusive-scraper');

        if ($maxResults > 100) {
            throw new \Exception('Max results cannot be greater than 100');
        }

        $page = $page ?? 1;

        try {
            $runId = $apify->runActor([
                'startUrls' => ["https://rumble.com/c/$id"],
                'scrapeChannelVideos' => true,
                'scrapeChannelPlaylists' => false,
            ]);

            $runData = $apify->waitForRunCompletion($runId);
            $datasetId = $runData['defaultDatasetId'];
            $data = $apify->getDatasetItems($datasetId);

            if (!isset($data[0]['videosList'])) {
                return [
                    'next' => $page + 1,
                    'hasNext' => false,
                    'results' => [],
                ];
            }

            $creatorDTO = new CreatorDTO(Platform::Rumble, $id);
            $creatorDTO->name = $data[0]['title'];
            $creatorDTO->description = $data[0]['description'];
            $creatorDTO->avatar_url = $data[0]['thumb'];
            $creatorDTO->banner_url = $data[0]['backsplash'] ?? null;
            $creatorDTO->kind = Kind::Creator;
            $id = explode('/', $data[0]['url'])[4]; // AlexJonesTV
            $creatorDTO->id = $id;
            $creatorDTO->platform = Platform::Rumble;


            $results = Tools::validateDTOs(array_map(function ($value) use ($creatorDTO) {

                $resultDTO = new ResultDTO(Platform::Rumble, Kind::Video);
                $contentDTO = new ContentDTO(Platform::Rumble, Kind::Video, $value['id']);

                $contentDTO->kind = Kind::Video;
                $contentDTO->name = $value['title'];
                $contentDTO->duration = $value['duration'];
                $contentDTO->publish_time = Carbon::make($value['upload_date']);
                $contentDTO->thumbnail_url = $value['thumb'];
                $contentDTO->creator_id = $creatorDTO->id;
                $contentDTO->tags = $value['tags'] ?? [];
                $contentDTO->description = $value['description'] ?? '';

                $resultDTO->content = $contentDTO;
                $resultDTO->creator = $creatorDTO;

                return $resultDTO;
            }, $data[0]['videosList']));

            return [
                'next' => $page + 1,
                'hasNext' => null,
                'results' => $results,
            ];
        } catch (Exception $e) {
            Log::error('Error in Rumble getCreatorVideos: ' . $e->getMessage());
            return [
                'next' => $page + 1,
                'hasNext' => false,
                'results' => [],
            ];
        }
    }

}
