<?php

namespace App\Helpers\PlatformAPIs;

use App\Enums\Kind;
use App\Enums\Platform;
use App\Helpers\PlatformAPIs\PlatformInterfaces\iIsPlatform;
use App\Helpers\PlatformAPIs\PlatformInterfaces\iSearchable;
use App\Helpers\ResultDTO;
use App\Helpers\SearchQueryDTO;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class Podcasts implements iIsPlatform, iSearchable
{

    public static function getPlatform(): Platform
    {
        return Platform::Podcasts;
    }

    public Client $client;

    public function __construct()
    {
        $this->client = new Client(
            ['base_uri' => 'https://itunes.apple.com/search?term=']
        );
    }


    private static function getiTunesUrl(array $params) : string
    {
        return 'https://itunes.apple.com/search?entity=podcast&' . http_build_query($params);
    }


    public static function search(SearchQueryDTO $searchQuery): array
    {
        return self::getPodcastsFromItunesResults(self::searchItunes($searchQuery));
    }
    public static function searchItunes(SearchQueryDTO $searchQuery)
    {
        try {
            $pod = new Podcasts();
            $response = $pod->client->get(
                self::getiTunesUrl([
                    "term" => $searchQuery->query,
                    "limit" => $searchQuery->max_results
                ]), ['auth' => ['user', 'pass']]);
        } catch (GuzzleException $e){
            return [
                "error" => $e->getMessage()
            ];
        }

        return json_decode($response->getBody())->results;
    }


    public static function getPodcastsFromItunesResults(array $results): array
    {
        $podcasts = [];
        foreach ($results as $result){
            $podcasts[] = self::parseRSS($result->feedUrl);
        }
        return $podcasts;
    }

    /**
     * @param string $rssFeedLink
     * @param int $maxResults
     * @param int $pageToken 1 indexed page you want
     * @param bool $reverseOrder set true for earliest episodes first
     * @return array
     */
    public static function parseRSS(string $rssFeedLink, int $maxResults = 20, int $pageToken = 1, bool $reverseOrder = false): array
    {
        try{
            $rss = simplexml_load_file($rssFeedLink);
        }
        catch (\Exception $e){
            return [
                "podcast" => null,
                "episodes" => null,
                "pageTokenInfo" => [
                    "nextPageToken" => null,
                    "prevPageToken" => null
                ],
                "totalResults" => null
            ];
        }
        $itunes_prefixed = $rss->children()->channel->children("itunes", true);
        $no_prefix = $rss->children()->channel->children();
        $episodeCount = $no_prefix->item->count();
        $podcast = new ResultDTO(Platform::Podcasts, Kind::Podcast);
        $podcast->rss_url = $rssFeedLink;
        $podcast->description = (string)$itunes_prefixed->summary;
        $podcast->subcategory_name = (string)$itunes_prefixed->category->attributes();
        $podcast->category_name = (string)$itunes_prefixed->category->category->attributes();
        $podcast->avatar_url = (string)$itunes_prefixed->image->attributes()->href?:(string)$no_prefix->image->url;
        $podcast->channel_name = (string)$no_prefix->title;
        $podcast->language = (string)$no_prefix->language;
        $podcast->explicit = (bool)$no_prefix->explicit;


        // Fill an $episodes array with PodcastEpisode DTOs until maxResults, taking into account the order ($reverseOrder)
        $episodes = array();
        for($index = $reverseOrder ? $episodeCount-1-(($pageToken-1)*$maxResults) : ($pageToken-1)*$maxResults;
            isset($no_prefix->item[$index]) && $index>=0 && ($reverseOrder ? $episodeCount-($pageToken*$maxResults)<=$index : $index+1<=$maxResults*$pageToken);
            $reverseOrder ? $index-- : $index++)
        {
            $episode = new ResultDTO(Platform::Podcasts, Kind::PodcastEpisode);
            $episode->result_index = $episodeCount-$index;
            $episode->podcast_episode_name = (string)$no_prefix->item[$index]->title[0];
            $episode->description = (string)$no_prefix->item[$index]->description;
            $episode->guid = $no_prefix->item[$index]->guid;
            $episode->publish_time = $no_prefix->item[$index]->pubDate;

            $episode->audio_url = ($no_prefix->item[$index]->enclosure)['url'] ?: $no_prefix->item[$index]->link;
            $episode->audio_file_type = (string)isset($no_prefix->item[$index]->enclosure->attributes()->type)?
                $no_prefix->item[$index]->enclosure->attributes()->type:
                $no_prefix->item[$index]->link;

            //set thumbnail if it exists else default thumbnail
            if(isset($no_prefix->item[$index]->children("itunes", true)->image->attributes()->href)){
                $episode->thumbnail_url = (string)$no_prefix->item[$index]->children("itunes", true)->image->attributes()->href;
            }
            else{
                $episode->thumbnail_url = $podcast->avatar_url;
            }

            // calculate duration
            $episode->duration = (string)$no_prefix->item[$index]->children("itunes", true)->duration;
            if(!is_numeric($episode->duration) && $episode->duration != "")
            {
                $episode->duration = convertTimeToSeconds($episode->duration)-1;
            }

            $episodes[] = $episode;
        }

        return [
            "podcast" => $podcast,
            "episodes" => $episodes,
            "pageTokenInfo" => [
                "nextPageToken" => $episodeCount>$maxResults*$pageToken ? $pageToken+1 : null,
                "prevPageToken" => $pageToken==1 ? null : $pageToken-1
            ],
            "totalResults" => $episodeCount,
            $itunes_prefixed,
            $no_prefix
        ];
    }

    public static function outputHtml($parsedRss) :string
    {
        $output = "";
        foreach ($parsedRss as $episode){
            $output.=
                '
                <p>'.($episode->result_index).'. '.convertDuration($episode->duration).' '.$episode->podcast_episode_name.'</p>
                <img src="'.$episode->thumbnail_url.'" width="120px">
                <audio controls>
                    <source src="'.$episode->audio_url.'" type="audio/mpeg">
                    Your browser does not support the audio element.
                </audio>
                <br>
                ';
        }
        $output.='<iframe style="border-radius:12px" src="https://open.spotify.com/embed/show/4rOoJ6Egrf8K2IrywzwOMk/video?utm_source=generator?theme=0" width="624" height="351" frameBorder="0" allowfullscreen="" allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture" loading="lazy"></iframe>';
        $output.='<br>';
        $output.='<iframe style="border-radius:12px" src="https://open.spotify.com/embed/episode/6dMVMejS0LwF3us4oYIInd?theme=0" width="100%" height="152" frameBorder="0" allowfullscreen="" allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture" loading="lazy"></iframe>';
        $output.='<br>';
        $output.='<iframe style="border-radius:12px" src="https://open.spotify.com/embed/episode/1W3USNVf6PhGUBz3vRFhLw?theme=0" width="100%" height="352" frameBorder="0" allowfullscreen="" allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture" loading="lazy"></iframe>';
        $output.='<br>';
        $output.='<iframe style="border-radius:12px" src="https://open.spotify.com/embed/episode/1W3USNVf6PhGUBz3vRFhLw?theme=0" width="100%" height="100" frameBorder="0" allowfullscreen="" allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture" loading="lazy"></iframe>';
        return $output;
    }





}
