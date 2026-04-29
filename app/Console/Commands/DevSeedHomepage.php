<?php

namespace App\Console\Commands;

use App\Helpers\VidgazeTrendFeedCache;
use App\Models\Category;
use App\Models\VideoModels\Video;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Makes local home-page rows non-empty when editorial categories have no videos and trend Redis has never been filled.
 * Does not replace production pipelines (Twitter trends, category discovery); use after migrate:fresh or an empty dev DB.
 */
class DevSeedHomepage extends Command
{
    protected $signature = 'dev:seed-homepage {--force : Run even when APP_ENV is not local}';

    protected $description = 'Spread existing videos across homepage category slugs and seed demo trend topics (local dev)';

    public function handle(): int
    {
        if (! $this->laravel->environment('local') && ! $this->option('force')) {
            $this->warn('Skipped: run only in APP_ENV=local, or pass --force.');

            return self::SUCCESS;
        }

        $slugRotation = [
            'vidgaze_picks',
            'music',
            'gaming',
            'crypto_currency',
            'alternate_news',
        ];

        $targets = [];
        foreach ($slugRotation as $slug) {
            $category = Category::where('slug', $slug)->first();
            if ($category === null) {
                $this->warn("Category slug missing: {$slug}");

                continue;
            }
            $targets[] = $category;
        }

        if ($targets === []) {
            $this->error('No homepage categories found. Run migrations / seeders.');

            return self::FAILURE;
        }

        $ids = Video::query()->orderBy('id')->pluck('id')->all();
        if ($ids === []) {
            $this->warn('No videos in the database; nothing to assign.');

            return self::SUCCESS;
        }

        $n = count($targets);
        foreach ($ids as $i => $vid) {
            $category = $targets[$i % $n];
            Video::where('id', $vid)->update([
                'category_id' => $category->id,
                'categorised' => true,
                'categorised_at' => Carbon::now(),
            ]);
        }

        $picks = Category::where('slug', 'vidgaze_picks')->first();
        if ($picks !== null) {
            $pickIds = Video::where('category_id', $picks->id)->orderBy('id')->limit(8)->pluck('id');
            Video::whereIn('id', $pickIds)->update(['pinned' => true]);
            $this->info('Pinned '.$pickIds->count().' videos in vidgaze_picks.');
        }

        $this->info('Assigned '.count($ids).' videos across '.count($targets).' homepage categories.');

        $sampleIds = array_slice($ids, 0, 12);
        $labels = ['Gaming highlights', 'Music moments', 'Breaking clips'];
        $topics = [];
        foreach ($labels as $i => $label) {
            $chunk = array_slice($sampleIds, $i * 3, 3);
            if ($chunk === []) {
                break;
            }
            $topics[] = [
                'key' => VidgazeTrendFeedCache::trendKey($label),
                'label' => $label,
                'video_ids' => $chunk,
            ];
        }
        if ($topics !== []) {
            VidgazeTrendFeedCache::replaceManifest($topics);
            $this->info('Seeded '.count($topics).' demo trend topic(s) in Redis.');
        }

        return self::SUCCESS;
    }
}
