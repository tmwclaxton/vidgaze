<?php

namespace App\Helpers\PlatformAPIs;

use App\Enums\Kind;
use App\Enums\Platform;
use App\Helpers\ContentDTO;
use App\Helpers\CreatorDTO;
use App\Helpers\ResultDTO;
use App\Helpers\SearchQueryDTO;
use Illuminate\Support\Arr;
use SpotifyWebAPI\Session;
use SpotifyWebAPI\SpotifyWebAPI;

class Spotify
{

    public Session $session;
    public SpotifyWebAPI $client;
    public function __construct()
    {
        $session = new Session(
            config('platforms.spotify.client_id'),
            config('platforms.spotify.client_secret'),
            config('platforms.spotify.redirect_uri')
        );
        $this->session = $session;
        $session->requestCredentialsToken();
        $this->client = (new SpotifyWebAPI())->setSession($session);
    }

    public static function getPlatform(): Platform
    {
        return Platform::Spotify;
    }


    // maximum of 50 ids

    /**
     * @throws \Exception
     */
    public static function getPodcasts(array $ids): array
    {
        if (count($ids) > 50) {
            throw new \Exception("Spotify can only take 50 ids at a time");
        }

        $spotify = new self();
        $response = $spotify->client->getShows($ids, [
            'market' => 'US'
        ]);

        return Arr::map($response->shows, function ($podcast_result) {
            return self::extractPodcastResultToDTO($podcast_result);
        });
    }
    public static function search(SearchQueryDTO $searchQueryDTO): array
    {
        $spotify = new self();

        $response = $spotify->client->search(
            $searchQueryDTO->query,
            ['show', 'episode'],
            [
                'limit' => $searchQueryDTO->max_results,
                'market' => 'US'
            ]
        );
        return Arr::map($response->shows->items, function ($podcast_result) {
            return self::extractPodcastResultToDTO($podcast_result);
        });
    }

    private static function extractPodcastResultToDTO($podcast_result): ResultDTO
    {
        $resultDTO = new ResultDTO(Platform::Spotify, Kind::Podcast);
        $resultDTO->creator = new CreatorDTO(Platform::Spotify, $podcast_result->id);
        $resultDTO->creator->name = $podcast_result->name;
        $resultDTO->creator->description = $podcast_result->description;
        $resultDTO->creator->avatar_url = $podcast_result->images[0]->url;
        $resultDTO->creator->language = $podcast_result->languages[0];

        $resultDTO->content = new ContentDTO(Platform::Spotify, Kind::Podcast, $podcast_result->id);
        $resultDTO->content->name = $podcast_result->name;
        $resultDTO->content->description = $podcast_result->description;
        $resultDTO->content->thumbnail_url = $podcast_result->images[0]->url;
        $resultDTO->content->language = $podcast_result->languages[0];
        return $resultDTO;
    }


    // max 50 episodes returned
    // offset is number of sets of $limit episodes to skip
    public static function getPodcastEpisodes(string $podcast_id, $limit = 50, int $offset = 0): array
    {
        $spotify = new self();
        $response = $spotify->client->getShowEpisodes($podcast_id, [
            'market' => 'US',
            'limit' => $limit,
            'offset' => $offset * $limit
        ]);
        return Arr::map($response->items, function ($episode_result) {
            return self::extractPodcastEpisodeResultToDTO($episode_result);
        });
    }

    private static function extractPodcastEpisodeResultToDTO($podcast_result): ContentDTO
    {
        $contentDTO = new ContentDTO(Platform::Spotify, Kind::Podcast, $podcast_result->id);
        $contentDTO->name = $podcast_result->name;
        $contentDTO->description = $podcast_result->description;
        $contentDTO->thumbnail_url = $podcast_result->images[0]->url;
        $contentDTO->language = $podcast_result->languages[0];
        return $contentDTO;
    }



    public static function exampleEmbed(string $id){
        $epIds = Arr::map(self::getPodcastEpisodes($id, 3), function($ep){
            return $ep->id;
        });
        $output = "";
//        $src = "https://open.spotify.com/embed/show/$id?theme=0";
//        $output .= "<iframe src=\"$src\" width=\"100%\" height=\"232\" frameborder=\"0\" allowtransparency=\"true\" allow=\"encrypted-media\"></iframe>";
//        $output .= '<br>';

        foreach($epIds as $epId){
            $epSrc = "https://open.spotify.com/embed-podcast/episode/$epId?theme=0";
            $output .= "<iframe src=\"$epSrc\" width=\"100%\" height=\"232\" frameborder=\"0\" allowtransparency=\"true\" allow=\"encrypted-media\"></iframe>";
            $output .= '<br>';
        }
        return $output;
    }

}
