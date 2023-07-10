<?php

namespace App\Console\Commands;

use App\Helpers\PlatformAPIs\Twitch;
use App\Helpers\SearchResultDTO;
use App\Http\Controllers\StreamController;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RefreshOneCategory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'refresh:one_twitch_category';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Look for a twitch category that needs updated and updates it';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $category = Category::where([
            ['twitch_category_id', '!=', null],
            ['updated_at', '<=', Carbon::now()->subHours(1)->toDateTimeString()]
        ])->orWhere([
            ['updated_at', '=', null],
            ['twitch_category_id', '!=', null]
        ])->get()->first();

        if ($category) {
            foreach (Twitch::getTopStreamsByCategory($category->twitch_category_id) as $stream) {
                SearchResultDTO::createStreamModelFromResultDTO($stream);
            }

            $category->touch(); // to update updated at attribute
            return Command::SUCCESS;
            //        dd( $category->id);
        }
        return null;
    }
}
