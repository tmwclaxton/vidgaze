<?php

namespace App\Jobs;

use App\Models\Award;
use App\Models\VideoModels\Video;
use App\Models\VideoModels\VideoAward;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RandomAwards implements ShouldQueue
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
        // grab videos that have views
        $videos = Video::where('view_count', '>', 0)->inRandomOrder()->limit(rand(0, 30))->get();
        $awards = Award::all();

        foreach ($videos as $video) {
            $awards = $awards->shuffle()->take(rand(1, 5));
            foreach ($awards as $award) {
                for ($i = 0; $i < rand(1, $video->view_count); $i++) {
                    $videoAward = new VideoAward();
                    $videoAward->video_id = $video->id;
                    $videoAward->award_id = $award->id;
                    $videoAward->save();
                }
            }
        }
    }
}
