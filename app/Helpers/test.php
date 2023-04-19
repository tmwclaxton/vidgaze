<?php

//namespace App\Helpers;
//require_once __DIR__.'/../../vendor/autoload.php';
//require_once __DIR__.'/../../config/database.php';
//require_once __DIR__.'/../../config/app.php';
//require_once __DIR__.'/../../.env';
//require_once __DIR__.'/../../config/platforms.php';

//require_once __DIR__.'/Search.php';

//use Google\Client;
//use Google_Service_YouTube;
//use Google\Service\YouTube\Resource\Youtube;
//require_once __DIR__.'/../../vendor/guzzlehttp/guzzle/src/Client.php';

//use GuzzleHttp\Client;

$GOOGLE_DEVELOPER_KEY="AIzaSyDvwejSRwuq8ieWWvyvCw0s2kjVtrPmHnE";
$GOOGLE_CLIENT_ID="470695778657-e2aq17m1nts89eeje9fp0brb0afi2hfh.apps.googleusercontent.com";
$GOOGLE_CLIENT_SECRET="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";


//$url = 'https://www.googleapis.com/youtube/v3/search';
//$params = array(
//    'part' => 'snippet',
//    'q' => 'hello',
//    'key' => $GOOGLE_DEVELOPER_KEY
//);
//
//// Initialize cURL and set the options
//$ch = curl_init();
//curl_setopt($ch, CURLOPT_URL, $url);
//curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//curl_setopt($ch, CURLOPT_POST, true);
//curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
//
//// Execute the cURL request and get the response
//$response = curl_exec($ch);
//
//// Close the cURL connection
//curl_close($ch);
//
//// Decode the JSON response
//$data = json_decode($response);
//
//// Print the results
//print_r($data);
// Set up the API endpoint URL and query parameters


function theFunction(){
    echo "hi";
    return "there";
}

if(isset($argv[1]) && function_exists($argv[1])) {
    $result = call_user_func($argv[1]);
    echo $result;
    return;
}

$start = microtime(true);


$url = 'https://itunes.apple.com/search';
$params = array(
    'term' => 'test',
    'entity' => 'podcast',
    'limit' => 3
);

// Initialize cURL and set the options
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url . '?' . http_build_query($params));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Execute the cURL request and get the response
$response = curl_exec($ch);

// Close the cURL connection
curl_close($ch);

// Decode the JSON response
$data = json_decode($response);

// Print the results
$time_elapsed_secs = microtime(true) - $start;
echo $time_elapsed_secs;
print_r($data);

//echo file_get_contents('https://itunes.apple.com/search?term=hello&limit=2');
return;
//$searchQuery = "hello";
//$maxResults = 1;
//$client = new Client(
//    ['base_uri' => 'https://itunes.apple.com/search?term=']
//);

//function getiTunesUrl(array $params) : string
//{
//    return 'https://itunes.apple.com/search?entity=podcast&' . http_build_query($params);
//}
//
//$response = $client->get(
//    getiTunesUrl([
//        "term" => $searchQuery,
//        "limit" => $maxResults
//    ]), ['auth' => ['user', 'pass']]);
//
//echo json_decode($response->getBody());





//print_r($response);








//use App\Helpers\PlatformAPIs\YouTube;
//use Illuminate\Support\Facades\Redis;
//use Predis\Client;
//
//$client = new Client();

//$searchQuery = "hello";
//$maxResults = 3;
//$key = "search:".$searchQuery;
//$timeKey = "search_time:".$searchQuery;


//$y_start = microtime(true);
//$results = YouTube::search($searchQuery, $maxResults)['results'];

//echo json_encode($results);
//$client->rPush($key, SearchResultDTO::jsonEncodeArray($results));
//SearchResultDTO::convertResultDTOToModels($results);
//$y_time_elapsed_secs = microtime(true) - $y_start;
//$client->sAdd($timeKey, "yt_time: ".$y_time_elapsed_secs);

//echo "done";
//echo Search::temp("\nyes");

//echo dirname(__DIR__, 2);;
//Redis::client()->set("toby", "sucks");
//echo file_get_contents(__DIR__.'/../../vendor/autoload.php');
//echo __DIR__.'/..'.'/..';
