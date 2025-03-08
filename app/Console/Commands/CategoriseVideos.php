<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\VideoModels\Video;
use Illuminate\Console\Command;

class CategoriseVideos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:categorise-videos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Use AI to categorise videos based on their title and description";

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // grab 50 videos without a category_id
        $videos = Video::whereNull('category_id')->limit(50)->get();

        // grab all categories
        $categories = Category::all();
    }
}
