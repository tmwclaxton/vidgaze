<?php

namespace App\Console\Commands;

use App\Enums\Platform;
use App\Models\CreatorModels\Creator;
use App\Models\CreatorModels\CreatorSource;
use Illuminate\Console\Command;

class getRumbleBanners extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:get-rumble-banners';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Rumble Creators without banners';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $rumble = new \App\Helpers\PlatformAPIs\Rumble();

        // grab all rumble creators without banners
        $creatorSources = CreatorSource::where('source_name', Platform::Rumble)
            ->get();

        // get all creators using the creator sources
        $creators = Creator::whereIn('id', $creatorSources->pluck('creator_id')->toArray())
            ->whereNull('banner_url')
            ->where('updated_at', '<', now()->subDay())
            ->orderBy('updated_at')
            ->get();

        // make sure the last_updated_at is updated
        $creators->each(function ($creator) {
            $creator->updated_at = now();
            $creator->save();
        });


        // attach the external_channel_id to the results
        $creators = $creators->map(function ($creator) use ($creatorSources) {
            $source = $creatorSources->where('creator_id', $creator->id)->first();
            $creator->external_channel_id = $source->external_channel_id;
            return $creator;
        });


        $creatorIds = $creators->pluck('external_channel_id')->toArray();
        // grab 20 creators at a time
        $creatorIds = array_chunk($creatorIds, 50);

        if (count($creatorIds) === 0) {
            return 0;
        }

        $rumbleCreators = $rumble->getCreators($creatorIds);

        $count = 0;
        foreach ($rumbleCreators as $rumbleCreator) {
            // grab creator through external_channel_id
            $creatorSource = CreatorSource::where('source_name', Platform::Rumble)
                ->where('external_channel_id', $rumbleCreator->creator->id)
                ->first();

            if (!$creatorSource) {
                continue;
            }

            $creator = Creator::find($creatorSource->creator_id);

            if ($creator) {
                if ($rumbleCreator->creator->banner_url) {
                    $count++;
                }
                $creator->banner_url = $rumbleCreator->creator->banner_url;
                $creator->bio = json_encode($rumbleCreator->creator->description);
                $creator->save();
            }
        }

        return $count;
    }
}
