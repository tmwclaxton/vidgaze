<?php


if(isset($argv[1])) {
    $url = $argv[1];

    // Initialize cURL and set the options
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Execute the cURL request and get the response
    $response = curl_exec($ch);

    // Close the cURL connection
    curl_close($ch);

    // Decode the JSON response
//    $data = json_decode($response);

    // Print the results
//    print_r($data);
    echo $response;

    return $response;
}
