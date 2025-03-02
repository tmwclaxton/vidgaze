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
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Rumble implements iSearchable, iIsPlatform
{
    private $apifyToken;

    public function __construct()
    {
        $this->apifyToken = env('APIFY_TOKEN');
    }

    public static function getPlatform(): Platform
    {
        return Platform::Rumble;
    }

    public function getCreator(string $id): CreatorDTO
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apifyToken,
        ])->post('https://api.apify.com/v2/acts/azzouzana~rumble-all-inclusive-scraper/run-sync-get-dataset-items', [
            'startUrls' => ["https://rumble.com/c/$id"],
            'scrapeChannelVideos' => false,
            'scrapeChannelPlaylists' => false,
        ]);

        $data = $response->json();

        $creatorDTO = new CreatorDTO(Platform::Rumble, $id);
        $creatorDTO->name = $data['name'] ?? '';
        $creatorDTO->description = $data['description'] ?? '';
        $creatorDTO->avatar_url = $data['avatar_url'] ?? '';
        $creatorDTO->region = $data['region'] ?? '';

        return $creatorDTO;
    }

//    public static function getVideo(string $id): ContentDTO
//    {
//        $response = Http::withHeaders([
//            'Authorization' => 'Bearer ' . env('APIFY_TOKEN'),
//        ])->post('https://api.apify.com/v2/acts/azzouzana~rumble-all-inclusive-scraper/run-sync-get-dataset-items', [
//            'startUrls' => ["https://rumble.com/$id"],
//        ]);
//
//        $data = $response->json();
//        dd($data);
//    }

    /**
     * @throws Exception
     */
    public static function grabEmbedLink(string $key, int $attempts = 6): array
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://rumble.com/service.php?video=$key&start=0&name=media.embed&included_js_libs=main,web_services,events,error,facebook_events,htmx.org,navigation-state,modal-base,darkmode,random,local_storage,notify,popout,tooltip,rac-ad,context-menus,provider,swipe-slider,ui,ads-north,search-bar,ui_header,main-menu-item-hover,premium-popup&included_css_libs=global",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 2,
            CURLOPT_FOLLOWLOCATION => true,
        ]);

        $response = curl_exec($curl);
        curl_close($curl);

        if (!$response) {
            if ($attempts > 1) {
                return self::grabEmbedLink($key, $attempts - 1);
            }
            throw new Exception("Failed to fetch embed link after 3 attempts.");
        }

        try {
            $response = json_decode($response, true);
            $html = $response["html"];

            $json = explode('Rumble("play", ', $html)[1] ?? '';
            $json = explode(');', $json)[0] ?? '';
            $json = json_decode($json, true);

            $videoId = $json['video'];
        } catch (Exception $e) {
            throw new Exception("Failed to parse embed link after 3 attempts.");
        }

        return [$videoId, $attempts];
    }

    public static function search(SearchQueryDTO $searchQueryDTO): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('APIFY_TOKEN'),
        ])->post('https://api.apify.com/v2/acts/azzouzana~rumble-all-inclusive-scraper/run-sync-get-dataset-items', [
            'startUrls' => ["https://rumble.com/search/all?q=" . urlencode($searchQueryDTO->query)],
        ]);

        $items = $response->json();

        // reverse the array to get the channels first
//        $items = array_reverse($items);

        $channelsWanted = 3;
        // check if item has a type attribute if so, check if it a user, if so remove
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

        // limit the results to 10
        $items = array_slice($items, 0, 20);

        return Arr::map($items, function ($value) {
            if (!isset($value['object_type']) && !isset($value['type'])) {
                return null;
            }

            $type = $value['object_type'] ?? $value['type'];

            switch ($type) {
                case "video":
                    // setup resultDTO
                    $resultDTO = new ResultDTO(Platform::Rumble, Kind::Video);
                    $resultDTO->platform = Platform::Rumble;

                    // setup contentDTO
                    // $key = explode('/', $value['videos'][0]['url'])[4]; // 6nqmlm
                    // dd($key,$value);
                    //
                    // $videoId = self::grabEmbedLink($key)[0];

                    //   "log" => array:1 [▼
                    //    "view" => "/l/view...5u0cqw.1jbbowy"
                    //  ]

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

                    // setup creatorDTO
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


        });
    }

    public static function getCreatorVideos(string $id, int $page = null, $maxResults = 100): array
    {
        if ($maxResults > 100) {
            throw new \Exception('Max results cannot be greater than 100');
        }

        $page = $page ?? 1;

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('APIFY_TOKEN'),
        ])->post('https://api.apify.com/v2/acts/azzouzana~rumble-all-inclusive-scraper/run-sync-get-dataset-items', [
            'startUrls' => ["https://rumble.com/c/$id/videos?page=$page"],
            'scrapeChannelVideos' => true,
            'scrapeChannelPlaylists' => false,
        ]);

        $data = $response->json();

        if (!isset($data['data'])) {
            return [
                'next' => $page + 1,
                'hasNext' => false,
                'results' => [],
            ];
        }

        $results = array_map(function ($value) {
            $contentDTO = new ContentDTO(Platform::Rumble, Kind::Video, $value['id']);

            $contentDTO->kind = Kind::Video;
            $contentDTO->name = $value['name'];
            $contentDTO->duration = $value['duration'];
            $contentDTO->publish_time = Carbon::make($value['publish_time']);
            $contentDTO->thumbnail_url = $value['thumbnail_url'];
            $contentDTO->creator_id = $value['creator_id'];
            $contentDTO->tags = array_map(fn($item) => $item['name'], $value['tags']);

            return $contentDTO;
        }, $data['data']);

        return [
            'next' => $page + 1,
            'hasNext' => boolval($data['paging']['next']),
            'results' => $results,
        ];
    }
}
