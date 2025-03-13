<?php

namespace App\Helpers\PlatformAPIs;

use Illuminate\Support\Facades\Http;

class Apify
{
    private mixed $token;
    private mixed $actor_id;

    public function __construct($actorId) {
        $this->token = env('APIFY_TOKEN');
        $this->actor_id = $actorId;
    }

    public function runActor(array $input): string {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->post("https://api.apify.com/v2/acts/$this->actor_id/runs", $input);

        return $response->json()['data']['id'];
    }

    public function waitForRunCompletion(string $runId): array {
        do {
            $run = $this->getRun($runId);
            sleep(5);
        } while (!in_array($run['status'], ['SUCCEEDED', 'FAILED', 'TIMED_OUT']));

        return $run;
    }

    public function getRun(string $runId): array {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->get("https://api.apify.com/v2/actor-runs/$runId");

        return $response->json()['data'];
    }

    public function getDatasetItems(string $datasetId): array {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->get("https://api.apify.com/v2/datasets/$datasetId/items");

        return $response->json();
    }
}
