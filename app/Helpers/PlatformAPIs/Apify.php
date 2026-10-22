<?php

namespace App\Helpers\PlatformAPIs;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Apify is only used as a fallback when crawl4ai fails.
 *
 * Every run is tuned for the lowest cost: one synchronous call
 * (run-sync-get-dataset-items, no polling), minimum memory, short timeout,
 * maxItems and maxTotalChargeUsd caps, no proxy unless configured, results
 * cached so identical inputs never start a second run, and a hard per day
 * cap on the number of runs.
 */
class Apify
{
    private ?string $token;

    public function __construct(private string $actorId)
    {
        $this->token = config('services.apify.token');
    }

    public static function enabled(): bool
    {
        return (bool) config('services.apify.fallback_enabled', true) && !empty(config('services.apify.token'));
    }

    public static function runsToday(): int
    {
        return (int) Cache::get(self::counterKey(), 0);
    }

    private static function counterKey(): string
    {
        return 'apify:fallback-runs:' . now()->format('Y-m-d');
    }

    /**
     * Run the actor and return its dataset items, or null if the run was
     * skipped (disabled, over the daily cap) or failed.
     *
     * @param  array  $input  actor input
     * @param  int  $maxItems  hard cap on charged results
     * @param  string  $reason  short label for the log line
     */
    public function run(array $input, int $maxItems, string $reason): ?array
    {
        if (!self::enabled()) {
            return null;
        }

        $cacheKey = 'apify:result:' . sha1($this->actorId . json_encode($input) . $maxItems);
        if (($cached = Cache::get($cacheKey)) !== null) {
            Log::info('Apify fallback reused cached result', ['actor' => $this->actorId, 'reason' => $reason]);
            return $cached;
        }

        $cap = (int) config('services.apify.daily_cap', 20);
        $used = self::runsToday();
        if ($used >= $cap) {
            Log::warning('Apify fallback skipped: daily cap reached', ['actor' => $this->actorId, 'reason' => $reason, 'cap' => $cap]);
            return null;
        }
        Cache::put(self::counterKey(), $used + 1, now()->endOfDay());

        $query = array_filter([
            'memory' => (int) config('services.apify.memory_mb', 512),
            'timeout' => (int) config('services.apify.timeout_secs', 90),
            'maxItems' => $maxItems,
            'maxTotalChargeUsd' => config('services.apify.max_charge_usd', 0.05),
            'clean' => 'true',
        ], fn ($v) => $v !== null && $v !== '');

        Log::info('Apify fallback run', [
            'actor' => $this->actorId,
            'reason' => $reason,
            'run_today' => $used + 1,
            'cap' => $cap,
            'memory' => $query['memory'],
            'timeout' => $query['timeout'],
            'maxItems' => $maxItems,
        ]);

        try {
            $response = Http::withToken($this->token)
                ->timeout($query['timeout'] + 30)
                ->post('https://api.apify.com/v2/acts/' . $this->actorId . '/run-sync-get-dataset-items?' . http_build_query($query), $input);

            if (!$response->successful()) {
                Log::warning('Apify fallback failed', ['actor' => $this->actorId, 'status' => $response->status()]);
                return null;
            }
            $items = array_slice((array) $response->json(), 0, $maxItems);
            Cache::put($cacheKey, $items, now()->addMinutes((int) config('services.apify.cache_minutes', 360)));
            return $items;
        } catch (Throwable $e) {
            Log::warning('Apify fallback error: ' . $e->getMessage(), ['actor' => $this->actorId]);
            return null;
        }
    }

    /**
     * Proxy block for actors that accept one. Off by default; set
     * APIFY_USE_PROXY=true for Apify datacenter proxy. Residential is only
     * used if APIFY_PROXY_GROUPS explicitly asks for it.
     */
    public static function proxyConfig(): array
    {
        if (!config('services.apify.use_proxy', false)) {
            return ['useApifyProxy' => false];
        }
        $groups = array_values(array_filter(explode(',', (string) config('services.apify.proxy_groups', ''))));
        return $groups ? ['useApifyProxy' => true, 'apifyProxyGroups' => $groups] : ['useApifyProxy' => true];
    }
}
