<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\CreatorModels\Creator;
use App\Models\VideoModels\Video;
use App\Models\VideoModels\VideoSource;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        Category::updateOrCreate(['id' => 1],[
            'name' => 'Movies',
            'slug' => 'movies',
            'youtube_category_id' => '1',
            'dailymotion_category_id' => 'shortfilms',
        ]);
        Category::updateOrCreate(['id' => 2],[
            'name' => 'Cars',
            'slug' => 'cars',
            'youtube_category_id' => '2',
            'dailymotion_category_id' => 'auto',
        ]);
        Category::updateOrCreate(['id' => 3],[
            'name' => 'Music',
            'slug' => 'music',
            'youtube_category_id' => '10',
            'dailymotion_category_id' => 'music',
            'twitch_category_id' => '26936',
        ]);
        Category::updateOrCreate(['id' => 4],[
            'name' => 'Pets & Animals',
            'slug' => 'pets_and_animals',
            'youtube_category_id' => '15',
            'dailymotion_category_id' => 'animals',
        ]);
        Category::updateOrCreate(['id' => 5],[
            'name' => 'Sports',
            'slug' => 'sports',
            'youtube_category_id' => '17',
            'dailymotion_category_id' => 'sport',
            'twitch_category_id' => '518203'
        ]);
        Category::updateOrCreate(['id' => 6],[
            'name' => 'Travel & Events',
            'slug' => 'travel_and_events',
            'youtube_category_id' => '19',
            'dailymotion_category_id' => 'travel',
        ]);
        Category::updateOrCreate(['id' => 7],[
            'name' => 'Gaming',
            'slug' => 'gaming',
            'youtube_category_id' => '20',
            'dailymotion_category_id' => 'videogames',
        ]);
        Category::updateOrCreate(['id' => 8],[
            'name' => 'People & Blogs',
            'slug' => 'people_and_blogs',
            'youtube_category_id' => '22',
            'dailymotion_category_id' => 'people',
            'twitch_category_id' => '509658' // created below so if you uncomment, bear in mind just chatting category
        ]);
        Category::updateOrCreate(['id' => 9],[
            'name' => 'Comedy',
            'slug' => 'comedy',
            'youtube_category_id' => '23',
            'dailymotion_category_id' => 'fun',
        ]);
        Category::updateOrCreate(['id' => 10],[
            'name' => 'Entertainment',
            'slug' => 'entertainment',
            'youtube_category_id' => '24',
            'dailymotion_category_id' => 'fun',
        ]);
        Category::updateOrCreate(['id' => 11],[
            'name' => 'News & Politics',
            'slug' => 'news_and_politics',
            'youtube_category_id' => '25',
            'dailymotion_category_id' => 'news',
        ]);
        Category::updateOrCreate(['id' => 12],[
            'name' => 'How-to & Lifestyle',
            'slug' => 'how-to_and_lifestyle',
            'youtube_category_id' => '26',
            'dailymotion_category_id' => 'lifestyle',
        ]);
        Category::updateOrCreate(['id' => 13],[
            'name' => 'Education',
            'slug' => 'education',
            'youtube_category_id' => '27',
            'dailymotion_category_id' => 'school',
        ]);
        Category::updateOrCreate(['id' => 14],[
            'name' => 'Science & Technology',
            'slug' => 'science_and_technology',
            'youtube_category_id' => '28',
            'dailymotion_category_id' => 'tech',
        ]);
        Category::updateOrCreate(['id' => 15],[
            'name' => 'Nonprofits & Activism',
            'slug' => 'nonprofits_and_activism',
            'youtube_category_id' => '29',
            'dailymotion_category_id' => 'news',
        ]);
    }
}
