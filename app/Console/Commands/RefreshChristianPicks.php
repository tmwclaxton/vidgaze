<?php

namespace App\Console\Commands;

use App\Enums\Platform;
use App\Helpers\ChristianPicks;
use App\Helpers\ResultDTO;
use App\Helpers\SearchQueryDTO;
use App\Models\Category;
use App\Models\VideoModels\Video;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class RefreshChristianPicks extends Command
{
    protected $signature = 'app:refresh-christian-picks {--queries=4 : How many queries to run} {--all : Run every query}';

    protected $description = 'Fill VidGaze Picks with Christian videos from YouTube and Rumble';

    public function handle(): int
    {
        $category = Category::where('slug', ChristianPicks::SLUG)->first();
        if (!$category) {
            $this->error('vidgaze_picks category missing');
            return self::FAILURE;
        }

        $queries = ChristianPicks::QUERIES;
        shuffle($queries);
        if (!$this->option('all')) {
            $queries = array_slice($queries, 0, (int) $this->option('queries'));
        }

        $added = 0;
        foreach ($queries as $q) {
            foreach ([Platform::YouTube, Platform::Rumble] as $platform) {
                $dto = new SearchQueryDTO($q, 15, [$platform]);
                if (count($dto->getPlatforms()) === 0) {
                    continue;
                }
                try {
                    $results = ResultDTO::convertArray(json_decode(json_encode($platform->getPlatformClass()::search($dto))));
                    $models = ResultDTO::saveAll($results);
                } catch (\Throwable $e) {
                    Log::warning("Christian picks search '{$q}' on {$platform->name} failed: " . $e->getMessage());
                    continue;
                }
                foreach ($models as $model) {
                    if (!$model instanceof Video) {
                        continue;
                    }
                    $creator = optional($model->creator()->first())->name;
                    if (!ChristianPicks::isChristian($model->title, $model->description, $creator)) {
                        continue;
                    }
                    if ($model->category_id !== $category->id) {
                        $added++;
                    }
                    $model->category_id = $category->id;
                    $model->categorised = true;
                    $model->categorised_at = Carbon::now();
                    if ($model->time_published && Carbon::parse($model->time_published)->diffInDays(now()) < 60) {
                        $model->pinned = true;
                        $model->pin_expires_at = now()->addDays(3);
                    }
                    $model->save();
                }
            }
        }

        $total = Video::where('category_id', $category->id)->count();
        $this->info("Added {$added} Christian videos, VidGaze Picks now has {$total}");
        return self::SUCCESS;
    }
}
