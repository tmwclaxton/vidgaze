<?php

namespace App\Console\Commands;

use App\Helpers\PlatformAPIs\Twitch;
use App\Models\Category;
use Illuminate\Console\Command;

class refreshTwitchCategoryInfoCommand extends Command
{
    protected $signature = 'refresh:twitch-category-info';

    protected $description = 'Gets old twitch category info and refreshes it (e.g. thumbnails)';

    public function handle()
    {
        $twitch_categories = Category::where("twitch_category_id", "!=", "null")->oldest()->take(30)->get();
        $twitch_cat_ids = $twitch_categories->map(fn ($cat) => $cat->twitch_category_id)->toArray();
        $data = Twitch::getCategories($twitch_cat_ids);

        \Arr::map($data, function ($item) use($twitch_categories){
            Category::where("twitch_category_id", '=', $item->category_id)->update([
                    'description' => $item->description?:null,
                    'thumbnail_url' => $item->category_thumbnail_url?:null,
                ]);
        });

    }
}
