<?php

namespace App\Helpers;

use App\Enums\Kind;
use App\Enums\Platforms;
use App\Helpers\PlatformAPIs\Dailymotion;
use App\Helpers\PlatformAPIs\Podcasts;
use App\Helpers\PlatformAPIs\Twitch;
use App\Helpers\PlatformAPIs\Vimeo;
use App\Helpers\PlatformAPIs\YouTube;
use App\Jobs\Search as SearchJob;
use Illuminate\Bus\Batch;
use Illuminate\Bus\PendingBatch;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Redis;
use Laravel\Octane\Facades\Octane;
use Predis\Client;
use Symfony\Component\Process\Process;
use function Deployer\Support\array_flatten;

class Search
{

    public static function cacheAsyncApiSearch(string $searchQuery, int $maxResults = 20, int $pageToken = null, array $filters = null, string $type = null){

        $key = "search:".$searchQuery;

        if(!Redis::client()->exists($key)) {

            $timeKey = "search_time:".$searchQuery;
            $start = microtime(true);

            for ($i = 0; $i < 5; $i++) {
                // Create a child process
                $pid = pcntl_fork();

                if ($pid == -1) {
                    // Fork failed
                    exit(1);
                } elseif ($pid) {
//                    sleep(4);
//                    dd("iuoip");
                    // This is the parent process
//                    break;
//                    while (!pcntl_wait($status, WNOHANG)) {
//                        $exitStatus = pcntl_wexitstatus($status);
//                        // Do something with the exit status of the child process
//                    }
//                    dd($pid);
//                pcntl_waitpid($pid, $status, WUNTRACED);
//                Redis::client()->set("stat", "done");
//                dd("awdawd");
//                    pcntl_wait($status);

                } else {
                    //child processes
//                Redis::client()->lPush("calls", 1);

                    switch ($i) {
                        case 0:
                            $y_start = microtime(true);
                            $results = YouTube::search($searchQuery, $maxResults)['results'];
                            Redis::client()->rPush($key, SearchResultDTO::jsonEncodeArray($results));
                            SearchResultDTO::convertResultDTOToModels($results);
                            $y_time_elapsed_secs = microtime(true) - $y_start;
                            Redis::client()->sAdd($timeKey, "yt_time: ".$y_time_elapsed_secs);
                            break;
                        case 1:
                            $d_start = microtime(true);
                            $results = Dailymotion::search($searchQuery, $maxResults)['results'];
                            Redis::client()->rPush($key, SearchResultDTO::jsonEncodeArray($results));
                            SearchResultDTO::convertResultDTOToModels($results);
                            $d_time_elapsed_secs = microtime(true) - $d_start;
                            Redis::client()->sAdd($timeKey, "d_time: ".$d_time_elapsed_secs);
                            break;
                        case 2:
                            $v_start = microtime(true);
                            $results = Vimeo::search($searchQuery, $maxResults)['results'];
                            Redis::client()->rPush($key, SearchResultDTO::jsonEncodeArray($results));
                            SearchResultDTO::convertResultDTOToModels($results);
                            $v_time_elapsed_secs = microtime(true) - $v_start;
                            Redis::client()->sAdd($timeKey, "v_time: ".$v_time_elapsed_secs);
                            break;
                        case 3:
                            $t_start = microtime(true);
                            $results = Twitch::search($searchQuery, 2)['results'];
                            Redis::client()->rPush($key, SearchResultDTO::jsonEncodeArray($results));
                            SearchResultDTO::convertResultDTOToModels($results);
                            $t_time_elapsed_secs = microtime(true) - $t_start;
                            Redis::client()->sAdd($timeKey, "t_time: ".$t_time_elapsed_secs);
                            break;
                        case 4:
                            $p_start = microtime(true);
                            $results = Podcasts::getPodcastsFromItunesResults(Podcasts::search($searchQuery, 2)["response"]->results);
                            Redis::client()->rPush($key, SearchResultDTO::jsonEncodeArray($results));
                            SearchResultDTO::convertResultDTOToModels($results);
                            $p_time_elapsed_secs = microtime(true) - $p_start;
                            Redis::client()->sAdd($timeKey, "p_time: ".$p_time_elapsed_secs);
                            break;
                    }
                    $i = 10000;
                    exit(0);
                }
            }
            //dd(pcntl_waitpid(-1, $status, WNOHANG));
//            sleep(3);
//            dd("tyuvbun");
//            $status = 0;
            error_log("0");

//            pcntl_wait($status);
            //pcntl_waitpid(0, $status); //never returns
//
//            while (pcntl_waitpid(0, $status) != -1) {
//                $status = pcntl_wexitstatus($status);
////                echo "Child $status completed\n";
//            }

//            Redis::client()->lPush("search_pid:".$searchQuery, $pid);
//            pcntl_wait($status);


            error_log("1");
//            dd("one"); //works
            // With this code, it waits just fine and code after wards exicuted fine but doesnt return out of the function
            //Without it, it returns fine but there is no data to access yet since it doesnt wait
            while(Redis::client()->lLen($key)<5)
            {
                usleep(500000);
            }

            error_log("llen: ".Redis::client()->lLen($key));
//            try{
//                while (is_numeric( Redis::client()->lLen($key)) && Redis::client()->lLen($key) < 5) {
//                    usleep(500000); // 0.5 seconds
//                }
//            }
//            catch (\Exception $e){
//                error_log($e->getMessage());
//            }

            error_log("2");


//            dd("two"); //doesn't work

            $time_elapsed_secs = microtime(true) - $start;
            Redis::client()->set("search_time_total:".$searchQuery, $time_elapsed_secs);

            Redis::client()->expire("search_time_total:".$searchQuery, config('system-variables.ttl_results'));
            Redis::client()->expire($timeKey, config('system-variables.ttl_results'));
            Redis::client()->expire($key, config('system-variables.ttl_results'));

            error_log("3");
            return false;

//            dd("asdffaaaagh");

//            try{
//            while (is_numeric( Redis::client()->lLen($key)) && Redis::client()->lLen($key) < 5) {
//                usleep(500000); // 0.5 seconds
////                pcntl_waitpid(-1, $status); //does this even do anything?
//            }

            //Redis::client()->lPush("test", '1');
            //dd("asdffgh");
//        }
//        catch (\Exception $e){
//            dd($e->getMessage());
//        }
//            return false;
        }
        return true; // already cached
    }


    public static function fun(){
        error_log("2");
        Redis::client()->set("toby", "sucks");
        sleep(3);
    }


    public static function temp(string $str){

        $client = new Client();
        $client->set("toby", "sucks");
        echo $client->get("toby");

        return $str;
    }
    public static function s(string $searchQuery = "", int $maxResults = 20){
        error_log("1");

//        dd([getcwd(), __FILE__, dirname(__FILE__)]);
        $processes = [];

        $start = microtime(true);

        // Create an array of Process instances, one for each script
        for ($i = 0; $i < 10; $i++) {
//            $arguments = ['php', __FILE__, 'Search::fun', $searchQuery, $maxResults];
//            $processes[$i] = new Process($arguments);

//            $processes[$i] = new Process(['ls', '-lsa', '../app/Helpers']);
//            $processes[$i] = new Process(['ls', '-lsa', dirname(__FILE__)]);
            //$processes[$i] = new Process(['sleep', 5]);

            $hi = 3;
            $process = null;
//            $processes[$i] = new Process(['php', __FILE__, 'Search::fun', $searchQuery, $maxResults]);
            $processes[$i] = new Process(['php', dirname(__FILE__)."/test.php", "theFunction"]);


//            dd(file_get_contents("https://www.google.com"));

            $comment = `\$processes[\$i] = new PhpProcess(<<<EOF
                    <?php
                    echo "hello";
                    //require_once 'vendor/autoload.php';
                    //getcwd();

                    //file_get_contents("https://www.google.com");
                    //__FILE__
                    //"yee";
                    //require_once 'vendor\laravel\framework\src\Illuminate\Support\Facades\Redis.php';

                    sleep(5);
                    //Redis::client()->set("toby", "sucks");


            EOF
            )`;
            $processes[$i]->start();
        }

        // Wait for all processes to finish
        foreach ($processes as $process) {
            $process->wait();
            if (!$process->isSuccessful()) {
                dd([
                        "Command returned exit code" => $process->getExitCode(),
                        "Error Output" => $process->getErrorOutput(),
                        "Process" => $process
                    ]
                );
            }
//            dd($process->getOutput());
            //dd($process);

        }

        $time_elapsed_secs = microtime(true) - $start;
//        dd($time_elapsed_secs);
        dd([
            $time_elapsed_secs,
            array_map(function ($p){
                return $p->getOutput();
            }, $processes)
        ]);

        dd($time_elapsed_secs);
    }

    public static function search2(string $searchQuery, int $maxResults = 20, int $pageToken = null, array $filters = null, string $type = null): array
    {
//        usleep(1000000);
//        dd("point 1"); //works
        $key = "search:".$searchQuery;
        $results = [];
        try{
            if(!self::cacheAsyncApiSearch($searchQuery, $maxResults))
            {
//                usleep(1000000);
//                dd("point 2"); //deosn't work
//                usleep(13100);
//                dd("point 3"); //works
//                $len = Redis::client()->lLen($key);
//
//                while(!is_numeric($len)){
//                    usleep(500000); // 0.5 seconds
//                    $len = Redis::client()->lLen($key);
//                }
////                dd($len);
//                while ($len < 5) {
////                    dd("one");
////                    sleep(1);
//                    usleep(500000); // 0.5 seconds
////                    dd("two");
//                    $len = Redis::client()->lLen($key);
////                pcntl_waitpid(-1, $status); //does this even do anything?
////                dd("three");
//                }
//                dd("four");

            }
            //dd("wes");
//            throw new \Exception();
            error_log("4");
        }
        catch (\Exception $e){
            error_log("error_1");
        }
        //dd("asdf");

        foreach (Redis::client()->lRange($key,0,-1) as $result){
            $results = array_merge($results, SearchResultDTO::jsonDecodeArray($result));
        }
        error_log("5");

        $creatorDTOs = [];
        $videoDTOs = [];
        $streamDTOs = [];
        $playlistDTOs = [];
        $podcastDTOs = [];

        /** @var SearchResultDTO $result */
        foreach ($results as $result) {
            match ($result->kind) {
                Kind::Creator => $creatorDTOs[] = $result,
                Kind::Video => $videoDTOs[] = $result,
                Kind::Stream => $streamDTOs[] = $result,
                Kind::Playlist => $playlistDTOs[] = $result,
                Kind::Podcast => $podcastDTOs[] = $result,
            };
        }
        error_log("6");

        //Redis::client()->lPush("here", '1');
        return [
            "creators" => SearchResultDTO::convertResultDTOToModels($creatorDTOs),
            "videos" => SearchResultDTO::convertResultDTOToModels($videoDTOs),
            "streams" => SearchResultDTO::convertResultDTOToModels($streamDTOs),
            "playlists" => SearchResultDTO::convertResultDTOToModels($playlistDTOs),
            "podcasts" => SearchResultDTO::convertResultDTOToModels($podcastDTOs)
        ];

//        return [
//            "creators" => null,
//            "videos" => null,
//            "streams" => null,
//            "playlists" => null,
//            "podcasts" => null
//        ];
    }

    /* List of valid filters
     *
     * ---- exclude platform ----
     * exclude_youtube
     * exclude_dailymotion
     * exclude_vimeo
     * exclude_twitch
     * exclude_podcasts
     * exclude_rumble
     * exclude_odysee
     *
     * exclude_video
     * exclude_stream
     * exclude_playlist
     * exclude_creator
     *
     */
    public static function search1(string $searchQuery, int $maxResults = 20, int $pageToken = null, array $filters = null, string $type = null): array
    {
        $creatorDTOs = [];
        $videoDTOs = [];
        $streamDTOs = [];
        $playlistDTOs = [];
        $podcastDTOs = [];

        $results = [];

        $start = microtime(true);

        $key = "search:".$searchQuery;
        if(!Redis::client()->exists($key) /*Redis::client()->ttl($key) && Redis::client()->ttl($key) <= 0*/){
            $results = array_merge($results, YouTube::search($searchQuery, $maxResults)['results']);
            $results = array_merge($results, Vimeo::search($searchQuery, $maxResults)['results']);
            $results = array_merge($results, Dailymotion::search($searchQuery, $maxResults)['results']);
//            $results = array_merge($results, Twitch::search($searchQuery, 2)['results']);
//            $results = array_merge($results, Podcasts::getPodcastsFromItunesResults(Podcasts::search($searchQuery, 3)["response"]->results));
            Redis::client()->sAdd($key, [
                SearchResultDTO::jsonEncodeArray($results)
            ]);
            Redis::client()->expireexpire($key, config('system-variables.ttl_results'));
            SearchResultDTO::arrayEnumConfigeration($results);
        }
        else{
            foreach (Redis::client()->sMembers($key) as $result){
                $results = array_merge($results, SearchResultDTO::jsonDecodeArray($result));
            }
        }

        $time_elapsed_secs = microtime(true) - $start;

//        dd([
//            "time taken"=>$time_elapsed_secs,
//            "results"=>$results
//        ]);


        /** @var SearchResultDTO $result */
        foreach ($results as $result) {
            match ($result->kind) {
                Kind::Creator => $creatorDTOs[] = $result,
                Kind::Video => $videoDTOs[] = $result,
                Kind::Stream => $streamDTOs[] = $result,
                Kind::Playlist => $playlistDTOs[] = $result,
                Kind::Podcast => $podcastDTOs[] = $result,
            };
        }

        return [
            "creators" => SearchResultDTO::convertResultDTOToModels($creatorDTOs),
            "videos" => SearchResultDTO::convertResultDTOToModels($videoDTOs),
            "streams" => SearchResultDTO::convertResultDTOToModels($streamDTOs),
            "playlists" => SearchResultDTO::convertResultDTOToModels($playlistDTOs),
            "podcasts" => SearchResultDTO::convertResultDTOToModels($podcastDTOs)
        ];

    }


    /**
     * gets all the keys of the cached searches
     * @return array
     * @throws \RedisException
     */
    public static function getCachedSearchQueries():array
    {
        $searches = Redis::client()->keys("search_*:*");
        $prefix = 'laravel_database_search:';

        foreach ($searches as &$search) {
            if (str_starts_with($search, $prefix)) {
                $search = substr($search, strlen($prefix));
            }
        }
        return $searches;
    }


    /**
     * performs an api search onn the platform specified in the $searchQuery DTO
     * @param SearchQueryDTO $searchQuery
     * @return array|string|null
     */
    public static function platformSearch(SearchQueryDTO $searchQuery, $platform){

        return match (strtolower($platform)) {
            'youtube' => YouTube::search($searchQuery->query, $searchQuery->maxResults),
            'dailymotion' => Dailymotion::search($searchQuery->query, $searchQuery->maxResults),
            'vimeo' => Vimeo::search($searchQuery->query, $searchQuery->maxResults),
            'twitch' => Twitch::search($searchQuery->query, $searchQuery->maxResults),
            'podcasts' => Podcasts::getPodcastsFromItunesResults(Podcasts::search($searchQuery->query, $searchQuery->maxResults)["response"]->results),
            default => null
        };
    }


    /**
     * batches a set of Search jobs and returns the PendingBatch object (before dispatch)
     * @param SearchQueryDTO $searchQuery
     * @return PendingBatch
     */
    public static function batchSearches(SearchQueryDTO $searchQuery): \Illuminate\Bus\PendingBatch
    {
        $platforms = $searchQuery->getPlatforms();

        $jobs = [];
        foreach ($platforms as $platform){
            $jobs[] = new SearchJob($searchQuery, $platform);
        }
        return Bus::batch($jobs);
    }


    /**
     * Dispatches an async batch of api searches, waits for all jobs in batch to conclude and caches the time taken
     * @param SearchQueryDTO $searchQuery
     * @return void
     * @throws \RedisException
     * @throws \Throwable
     */
    public static function asyncSearch(SearchQueryDTO $searchQuery): void
    {
        $platforms = $searchQuery->getPlatforms();

        $start = microtime(true);
        $batch = self::batchSearches($searchQuery);
        $batch->finally(function (Batch $batch) use ($searchQuery){
            Redis::client()->lPush(SearchQueryDTO::getRedisBatchKey($searchQuery->query), true);
        })->dispatch();

        // wait until batch has finished
        Redis::client()->blPop(SearchQueryDTO::getRedisBatchKey($searchQuery->query), 0);

        // set time taken for batch search
        Redis::client()->set(SearchQueryDTO::getRedisBatchTimeKey($searchQuery->query), microtime(true) - $start);
    }


    /**
     * Caches/gets cached search results before separating them into Kinds and converting/ returning Models
     * @param SearchQueryDTO $searchQuery
     * @return array
     * @throws \RedisException
     * @throws \Throwable
     */
    public static function search(SearchQueryDTO $searchQuery): array
    {
        self::asyncSearch($searchQuery);
        $keys = Arr::map($searchQuery->getPlatforms(), function ($platform) use ($searchQuery){
           return SearchQueryDTO::getRedisPlatformSearchKey($platform, $searchQuery->query);
        });


        $results = [];
        foreach ($keys as $key){
            $results[] =Redis::client()->get($key);
        }
        dd($results);
        foreach ($keys as $key){
            $results = array_merge($results, json_decode(Redis::client()->get($key)));
        }

        $creatorDTOs = [];
        $videoDTOs = [];
        $streamDTOs = [];
        $playlistDTOs = [];
        $podcastDTOs = [];

        /** @var SearchResultDTO $result */
        foreach ($results as $result) {
            match ($result->kind) {
                Kind::Creator => $creatorDTOs[] = $result,
                Kind::Video => $videoDTOs[] = $result,
                Kind::Stream => $streamDTOs[] = $result,
                Kind::Playlist => $playlistDTOs[] = $result,
                Kind::Podcast => $podcastDTOs[] = $result,
            };
        }

        return [
            "creators" => SearchResultDTO::convertResultDTOToModels($creatorDTOs),
            "videos" => SearchResultDTO::convertResultDTOToModels($videoDTOs),
            "streams" => SearchResultDTO::convertResultDTOToModels($streamDTOs),
            "playlists" => SearchResultDTO::convertResultDTOToModels($playlistDTOs),
            "podcasts" => SearchResultDTO::convertResultDTOToModels($podcastDTOs)
        ];
    }

    public static function octaneSearch(SearchQueryDTO $searchQuery)
    {

        $creatorDTOs = [];
        $videoDTOs = [];
        $streamDTOs = [];
        $playlistDTOs = [];
        $podcastDTOs = [];

        $results = [];

        $key = "search:".$searchQuery->query;
        if(!Redis::client()->exists($key) /*Redis::client()->ttl($key) && Redis::client()->ttl($key) <= 0*/){

            $start = microtime(true);

            $responses = Octane::concurrently(
                [
                    fn()=>YouTube::search($searchQuery->query, $searchQuery->maxResults)['results'],
                    fn()=>Vimeo::search($searchQuery->query, $searchQuery->maxResults)['results'],
                    fn()=>Dailymotion::search($searchQuery->query, $searchQuery->maxResults)['results'],
                    fn()=>Twitch::search($searchQuery->query, 2)['results'],
//                    fn()=>Podcasts::getPodcastsFromItunesResults(Podcasts::search($searchQuery->query, 3)["response"]->results)['results']
                ], 13000);

            $results = array_merge(...$responses);

            Redis::client()->set($key, json_encode($results));
            Redis::client()->expire($key, config('system-variables.ttl_results'));

            Redis::client()->set("search_time:".$searchQuery->query, microtime(true) - $start);
        }
        else{
            $results = json_decode(Redis::client()->get($key));
            SearchResultDTO::arrayEnumConfigeration($results);
        }



        /** @var SearchResultDTO $result */
        foreach ($results as $result) {
            match (Kind::fromValue($result->kind)) {
                Kind::Creator => $creatorDTOs[] = $result,
                Kind::Video => $videoDTOs[] = $result,
                Kind::Stream => $streamDTOs[] = $result,
                Kind::Playlist => $playlistDTOs[] = $result,
                Kind::Podcast => $podcastDTOs[] = $result,
            };
        }

        return [
            "creators" => SearchResultDTO::convertResultDTOToModels($creatorDTOs),
            "videos" => SearchResultDTO::convertResultDTOToModels($videoDTOs),
            "streams" => SearchResultDTO::convertResultDTOToModels($streamDTOs),
            "playlists" => SearchResultDTO::convertResultDTOToModels($playlistDTOs),
            "podcasts" => SearchResultDTO::convertResultDTOToModels($podcastDTOs)
        ];

    }


}

