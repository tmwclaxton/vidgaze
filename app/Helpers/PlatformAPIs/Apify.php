<?php

namespace App\Helpers\PlatformAPIs;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Apify
{
    public function __construct(
        private string $actor_id,
        private ?string $token = null,
    ) {
        $this->token = $token ?? (string) config('services.apify.token');
    }

    public function runActor(array $input): ?string
    {
        if ($this->token === '') {
            Log::warning('Apify runActor: missing APIFY_TOKEN.');

            return null;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->token,
                'Content-Type' => 'application/json',
            ])
                ->timeout(120)
                ->connectTimeout(25)
                ->post("https://api.apify.com/v2/acts/{$this->actor_id}/runs", $input);
        } catch (\Throwable $e) {
            Log::warning('Apify runActor transport error: '.$e->getMessage());

            return null;
        }

        $json = $response->json();
        if (! $response->successful()) {
            Log::warning('Apify runActor HTTP error', [
                'status' => $response->status(),
                'actor' => $this->actor_id,
                'body' => $json ?? substr($response->body(), 0, 600),
            ]);

            return null;
        }

        $id = $json['data']['id'] ?? null;
        if (! is_string($id) || $id === '') {
            Log::warning('Apify runActor: missing run id in response', ['json' => $json]);

            return null;
        }

        return $id;
    }

    public function waitForRunCompletion(string $runId): ?array
    {
        $deadline = time() + 3600;
        do {
            $run = $this->getRun($runId);
            if ($run === null) {
                Log::warning('Apify getRun returned null', ['runId' => $runId]);

                return null;
            }
            $status = $run['status'] ?? 'UNKNOWN';
            if (in_array($status, ['SUCCEEDED', 'FAILED', 'TIMED_OUT', 'ABORTED'], true)) {
                if ($status !== 'SUCCEEDED') {
                    Log::warning('Apify run finished unsuccessfully', ['runId' => $runId, 'status' => $status]);
                }

                return $run;
            }
            sleep(5);
        } while (time() < $deadline);

        Log::warning('Apify waitForRunCompletion: timed out waiting for run', ['runId' => $runId]);

        return null;
    }

    public function getRun(string $runId): ?array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->token,
            ])
                ->timeout(60)
                ->get("https://api.apify.com/v2/actor-runs/$runId");
        } catch (\Throwable $e) {
            Log::warning('Apify getRun transport error: '.$e->getMessage());

            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        $json = $response->json();

        return is_array($json['data'] ?? null) ? $json['data'] : null;
    }

    public function getDatasetItems(string $datasetId): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->token,
            ])
                ->timeout(180)
                ->get("https://api.apify.com/v2/datasets/$datasetId/items");
        } catch (\Throwable $e) {
            Log::warning('Apify getDatasetItems transport error: '.$e->getMessage());

            return [];
        }

        if (! $response->successful()) {
            Log::warning('Apify getDatasetItems HTTP error', ['status' => $response->status()]);

            return [];
        }

        $json = $response->json();

        return is_array($json) ? $json : [];
    }
}
