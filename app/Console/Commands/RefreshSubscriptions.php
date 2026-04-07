<?php

namespace App\Console\Commands;

use App\Jobs\RefreshCreatorChannelContent;
use App\Models\CreatorModels\CreatorInteraction;
use App\Models\VideoModels\VideoView;
use Illuminate\Console\Command;

class RefreshSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'refresh:subscriptions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Queue platform content refreshes for channels subscribed by viewers who were active recently';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $viewerIds = VideoView::query()
            ->where('created_at', '>=', now()->subDay())
            ->whereNotNull('viewer_id')
            ->distinct()
            ->pluck('viewer_id');

        if ($viewerIds->isEmpty()) {
            return Command::SUCCESS;
        }

        $creatorIds = CreatorInteraction::query()
            ->whereIn('viewer_id', $viewerIds)
            ->where('subscribed', true)
            ->distinct()
            ->pluck('creator_id')
            ->unique()
            ->take(40)
            ->values();

        foreach ($creatorIds as $creatorId) {
            RefreshCreatorChannelContent::dispatch((int) $creatorId)->onQueue('commands');
        }

        return Command::SUCCESS;
    }
}
