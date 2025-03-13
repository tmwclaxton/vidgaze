<?php

namespace App\Http\Controllers\Tools;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class NanoController
{
    private $baseUrl;
    private $apiKey;
    private $client;

    /**
     * Constructor to initialize API base URL, API key, and Guzzle client.
     */
    public function __construct()
    {
        $this->baseUrl = 'https://nano-gpt.com/api/v1'; // Correct base URL
        $this->apiKey = env('NANOGPT_API_KEY');        // Ensure you set this in your .env file
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    /**
     * Generate chat completions using NanoGPT API.
     *
     * @param array $messages Array of messages with 'role' and 'content'.
     * @param string $model Model name (default: 'deepseek-r1-nano').
     * @param array $options Optional generation settings like temperature, max_tokens, etc.
     * @return array The response decoded from JSON.
     *
     * @throws \Exception Throws exception if the API request fails.
     */
    public function getChatCompletion(array $messages, string $model = 'deepseek-r1-nano', array $options = [], bool $randomness = false)
    {
        $baseParams = [
            'max_tokens' => $options['max_tokens'] ?? 150,
            'temperature' => $options['temperature'] ?? 1.0,
            'top_p' => $options['top_p'] ?? 1.0,
        ];

        if ($randomness) {
            $baseParams['temperature'] = rand(1, 100) / 100;
            $baseParams['top_p'] = rand(1, 100) / 100;
        }

        // Prepare the payload for the API request
        $payload = array_merge([
            'model' => $model,
            'messages' => $messages,
            'temperature' => $baseParams['temperature'],
            'max_tokens' => $baseParams['max_tokens'],
            'top_p' => $baseParams['top_p'],
        ], $options);

        try {
            // Perform the HTTP POST request
            $response = $this->client->post('/api/v1/chat/completions', [
                'json' => $payload,  // Pass the payload as JSON
            ]);

            // Decode and return the response as an array
            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            // Handle exceptions and throw meaningful error messages
            throw new \Exception(
                'NanoGPT API request failed: ' . $e->getMessage()
            );
        }
    }
}
