<?php

namespace App\Jobs;

use App\Models\VideoModels\Video;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RandomViews implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // preferentially select videos that already have views, but still select videos with no views

        $averageViewCount = Video::avg('view_count');

        $alreadyViewed = Video::where('view_count', '>', $averageViewCount)->inRandomOrder()->limit(rand(0,30))->get();
        $notViewed = Video::where('view_count', '=', 0)->inRandomOrder()->limit(rand(20,50))->get();
        $videos = $alreadyViewed->merge($notViewed);
        foreach ($videos as $video) {
            $video->view_count += rand(1, 10);
            $video->live_viewer_count = rand(0, 1);
            $video->save();
        }
    }
}
