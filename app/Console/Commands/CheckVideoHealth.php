<?php

namespace App\Console\Commands;

use App\Models\VideoModels\Video;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Marks videos whose thumbnail is gone as unavailable so the recommender
 * skips them. A removed YouTube video loses its hqdefault thumbnail (404);
 * Rumble thumbnails 404 when the video is taken down.
 */
class CheckVideoHealth extends Command
{
    protected $signature = 'app:check-video-health {--limit=300}';

    protected $description = 'Check thumbnails and hide videos that are gone';

    public function handle(): int
    {
        $videos = Video::query()->without(['creator', 'category'])
            ->select(['id', 'thumbnail_url', 'available'])
            ->orderByRaw('health_checked_at IS NOT NULL, health_checked_at')
            ->limit((int) $this->option('limit'))
            ->get();

        $bad = 0;
        foreach ($videos->chunk(25) as $chunk) {
            $responses = Http::pool(function ($pool) use ($chunk) {
                foreach ($chunk as $v) {
                    $pool->as((string) $v->id)->timeout(8)->withHeaders(['User-Agent' => 'Mozilla/5.0'])->head($v->thumbnail_url ?: 'http://invalid.invalid');
                }
            });
            foreach ($chunk as $v) {
                $r = $responses[(string) $v->id] ?? null;
                // Network errors are not proof the video is gone: keep it.
                $ok = !($r instanceof \Illuminate\Http\Client\Response) || !in_array($r->status(), [404, 410], true);
                if (!$v->thumbnail_url) {
                    $ok = false;
                }
                if (!$ok) {
                    $bad++;
                }
                Video::whereKey($v->id)->update(['available' => $ok, 'health_checked_at' => now()]);
            }
        }
        $this->info("Checked {$videos->count()} videos, {$bad} unavailable");
        return self::SUCCESS;
    }
}
