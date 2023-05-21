<?php

namespace App\Helpers;

use App\Enums\Kind;
use App\Enums\Platforms;
use App\Models\Category;
use App\Models\CreatorModels\Creator;
use App\Models\CreatorModels\CreatorSource;
use App\Models\CreatorModels\TwitchLogin;
use App\Models\PodcastEpisodeModels\PodcastEpisode;
use App\Models\PodcastModels\Podcast;
use App\Models\StreamModels\Stream;
use App\Models\StreamModels\StreamSource;
use App\Models\VideoModels\Video;
use App\Models\VideoModels\VideoSource;
use Carbon\Carbon;

class SearchResultDTO
{
    // From external search API
    public Kind | string $kind; //must serialise enums before json encoding
    public Platforms | string $platform;
    public string $video_id;
    public string $playlist_id;
    public string $channel_id;
    public string $stream_id;
    public string $category_id;
    public string $category_name;
    public string $category_slug;
    public string $category_description;
    public string $category_thumbnail_url;
    public bool $assignable;
    public string $channel_name;
    public  $publish_time;
    public  $upload_time;
    public  $views;
    public  $likes;
    public  $dislikes;
    public  $avatar_url;
    public  $banner_url;
    public  $thumbnail_url;
    public  $description;
    public $bio;
    public $region;
    public $language;
    public array $tags;
    public string $video_name;
    public string $stream_name;
    public string $playlist_name;
    public string $twitch_login;
    public string $duration;
    public bool $is_live;
    public bool $explicit;

//    Podcast Specific Variables
    public string $podcast_id;
    public string $podcast_episode_id;
    public string $audio_url;
    public string $rss_url;
    public string $guid;
    public string $subcategory_name;
    public string $podcast_episode_name;
    public string $audio_file_type;
    public int $result_index;

    public Creator $creator;
    public Video $video;
    public Category $category;
    public Podcast $podcast;
    public PodcastEpisode $podcastEpisode;

    public static function convertResultDTOToModels(null | array $results, bool $brandingSettings = true): array //Models
    {
        $models = [];
        if($results){
            foreach($results as $result){
//                try {
                    $models[] = match ($result->kind) {
                        Kind::Video => self::createVideoModelFromResultDTO($result),
                        Kind::Creator => self::createCreatorModelFromResultDTO($result),
                        Kind::Stream => self::createStreamModelFromResultDTO($result),

                        Kind::RSS => self::createModelsFromRSSResultDTO($result),
                        Kind::Podcast => self::createPodcastModelFromResultDTO($result),
                        Kind::PodcastEpisode => self::createPodcastEpisodeModelFromResultDTO($result),
                        //Kind::Playlist => self::createPlaylistModelFromResultDTO($result),

                        Kind::Category => self::translateOrCreateCategory($result),
                        default => null,
                    };
//                }
//                catch (\Exception $e){}
            }
        }
        return $models;
    }

    public static function createStreamModelFromResultDTO(\stdClass | SearchResultDTO | null $result, Creator $creator = null): \Illuminate\Database\Eloquent\Model|Stream | null
    {
        if(!$result){
            return null;
        }
        $creator = $creator ?? Creator::findOrCreate($result->channel_id, $result->platform);
        $var = StreamSource::where('source_name', '=', $result->platform->name)
            ->where('external_id', '=', $result->stream_id)
            ->firstOr(function () use ($result, $creator){
                $stream = Stream::create([
                    'creator_id' => $creator->id,
                    'preferred_source' => $result->platform->name,
                    'title' => $result->stream_name,
                    'description' => $result->description,
                    'tags' => json_encode($result->tags ?? null),
                    'started_at' => Carbon::make($result->publish_time),
                    'thumbnail_url' => $result->thumbnail_url,
                    'category_id' => self::translateOrCreateCategory($result)->id,
                    //'language' => $snippet['defaultLanguage'],
                    'slug' => generateRandomString(),
                    'is_live' => $result->is_live??0
                ]);
                StreamSource::create([
                    'source_name' => $result->platform->name,
                    'external_id' => $result->stream_id,
                    'stream_id' => $stream->id,
                ]);
                return $stream;
            });
        return $var instanceof Stream? $var : $var->stream;
    }

    public static function createCreatorModelFromResultDTO(\stdClass | SearchResultDTO $result): \Illuminate\Database\Eloquent\Model|Creator | null
    {
        if(!$result){
            return null;
        }
        $var = CreatorSource::where('source_name', '=', $result->platform->name)
            ->where('external_channel_id', '=', $result->channel_id)
            ->firstOr(function () use ($result){
                $creator = Creator::create([
                    'slug' => $result->platform->getPrefix().'_'.$result->channel_id,
                    'name' => $result->channel_name,
                    'avatar_url' => $result->avatar_url,
                    'banner_url' => $result->banner_url,
                    'bio' => json_encode($result->bio),
                    'is_live' => $result->is_live??0
//                    'region' => $result->region,
                    //'language' => $result->language,
                ]);
                $source = CreatorSource::create([
                    'source_name' => $result->platform->name,
                    'external_channel_id' => $result->channel_id,
                    'creator_id' => $creator->id,
                ]);
                if($result->platform == Platforms::Twitch){
                    TwitchLogin::create([
                        'twitch_source_id' => $source->external_channel_id,
                        'twitch_channel_login' => $result->twitch_login
                    ]);
                }
                return $creator;
            });
        return $var instanceof Creator? $var : $var->creator;
    }

    public static function createVideoModelFromResultDTO(\stdClass | SearchResultDTO $result, $creator = null): \Illuminate\Database\Eloquent\Model|Video | null
    {
        $creator = $creator ?? Creator::findOrCreate($result->channel_id, $result->platform);
        $var = VideoSource::where('source_name', '=', $result->platform->name)
            ->where('external_id', '=', $result->video_id)
            ->firstOr(function () use ($result, $creator){
            $video = Video::create([
                'creator_id' => $creator->id,
                'preferred_source' => $result->platform->name,
                'title' => $result->video_name,
                'description' => $result->description,
                'duration' => $result->duration,
                'tags' => json_encode($result->tags ?? null),
                'time_published' => Carbon::make($result->publish_time)->timestamp,
                'thumbnail_url' => $result->thumbnail_url,
                'slug' => generateRandomString(),
                'category_id' => self::translateOrCreateCategory($result)?self::translateOrCreateCategory($result)->id:null,
                //'language' => $snippet['defaultLanguage'],
            ]);
            VideoSource::create([
                'source_name' => $result->platform->name,
                'external_id' => $result->video_id,
                'video_id' => $video->id,
            ]);
            return $video;
        });
       return $var instanceof Video? $var : $var->video;
    }

    public static function translateOrCreateCategory(\stdClass | SearchResultDTO $category): Category | null
    {
        if(!isset($category->category_id))
        {
            return null;
        }
        return Category::where($category->platform->getCategoryIdAttribute(), '=', $category->category_id)
        ->firstOr(function () use ($category){
            return Category::create([
                $category->platform->getCategoryIdAttribute() =>  $category->category_id,
                'slug' => convertNameToSlug($category->category_slug),
                'name' => $category->category_name,
                'description' => $category->description?:null,
                'thumbnail_url' => $category->category_thumbnail_url??null,
            ]);
        });
    }

    private static function createPodcastModelFromResultDTO(\stdClass | SearchResultDTO $result, $creator = null): ?Podcast
    {
        if(!$result->rss_url) return null;

        // check if podcast exists (by rss), (first Podcast or make Creator and Podcast)
        // if podcast is not found then it also has no creator, but if it is found then it does
        return Podcast::where('rss_url', '=', $result->rss_url)->firstOr(
            function () use ($creator, $result){
                $creator = $creator ?? Creator::create([
                    'slug' => Platforms::Podcasts->getPrefix().hash("md5", $result->rss_url),
                    'name' => $result->channel_name,
                    'avatar_url' => $result->avatar_url,
                    'bio' => json_encode( $result->description),
                    //'region' => 'usa',
                    //'category_id' => $cat2->id,
                ]);
                return Podcast::create([
                    'creator_id' => $creator->id,
                    'rss_url' =>  $result->rss_url,
                    //'category_id' => $cat2->id,
                ]);
            });
    }

    private static function createPodcastEpisodeModelFromResultDTO(\stdClass | SearchResultDTO $result, Podcast $podcast): ?PodcastEpisode
    {
        return PodcastEpisode::firstOrCreate([
            "slug" => $result, // guid
        ],[
            'podcast_id' => $podcast->id,
            "time_published" => Carbon::make($result->publish_time,),
            "thumbnail_url" => $result->thumbnail_url,
            "description" => $result->description,
            "audio_url" => $result->audio_url,
            "title" => $result->podcast_episode_name,
            'duration' => $result->duration,
        ]);
    }

    /**
     * @param array $result consists of podcast and episode DTOs
     * @param Creator|null $creator pass in Creator for efficiency
     * @return Podcast|null
     */
    private static function createModelsFromRSSResultDTO(array $result, Creator $creator = null): ?Podcast
    {
        if(!$result['podcast']) return null;

        // get Podcast Model
        $podcast = self::createPodcastModelFromResultDTO($result['podcast'], $creator);
        if(!isset($creator)) $creator = $podcast->creator();

        // if the $results also have episodes then create the PodcastEpisode Models
        foreach ($result['episodes'] as $episode){
            self::createPodcastEpisodeModelFromResultDTO($episode, $podcast);
        }

        return $podcast;
    }

    public function jsonEncode():string
    {
        $this->platform = $this->platform->jsonSerialize();
        $this->kind =  $this->kind->jsonSerialize();
        return  json_encode($this);
    }
    public static function jsonEncodeArray(array $results):string
    {
        /* @var $result SearchResultDTO */
        foreach ($results as $result){
            $result->platform = $result->platform->jsonSerialize();
            $result->kind =  $result->kind->jsonSerialize();
        }
        return json_encode($results);
    }

    public static function jsonDecode(string $item)
    {
        $result = json_decode($item);
        self::enumConfigeration($result);
        return  $result;
    }

    public static function enumConfigeration($result){
        $result->platform = Platforms::fromValue($result->platform);
        $result->kind = Kind::fromValue($result->kind);
        return  $result;
    }
    public static function arrayEnumConfigeration(array $results){
        foreach ($results as $result) {
            self::enumConfigeration($result);
        }
        return  $results;
    }

    public static function jsonDecodeArray(string $items):array
    {
        $results = json_decode($items);
        /* @var $result SearchResultDTO */
        foreach ($results as $result) {
            $result->platform = unserialize($result->platform);
            $result->kind = unserialize($result->kind);
        }
        return $results;
    }

}
