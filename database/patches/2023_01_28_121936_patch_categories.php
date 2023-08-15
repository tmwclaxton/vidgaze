<?php

use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
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
            'twitch_category_id_category_id' => '26936',
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


        $categories = [
            [
                'slug' => 'league_of_legends',
                'name' => 'League of Legends',
                'description' => "League of Legends is a fast-paced, competitive online game that blends the speed and intensity of an RTS with RPG elements. Two teams of powerful champions, each with a unique design and playstyle, battle head-to-head across multiple battlefields and game modes. With an ever-expanding roster of champions, frequent updates and a thriving tournament scene, League of Legends offers endless replayability for players of every skill level. ",
                'thumbnail_url' => 'https://static-cdn.jtvnw.net/ttv-boxart/21779-188x250.jpg',
                'youtube_category_id' => '',
                'twitch_category_id' => '21779',
                'dailymotion_category_id' => '',
                'tags_json' => json_encode(['MOBA','Action']),
                'updated_at' => Carbon::now()->subHours(1)->toDateTimeString()

            ],
            [
                'slug' => 'just_chatting',
                'name' => 'Just Chatting',
                'description' => "A category for streams that lack gameplay or a structured presentation, but rather are focused on a conversation between the streamer and other streamers or between the streamer and the viewers.",
                'thumbnail_url' => 'https://static-cdn.jtvnw.net/ttv-boxart/509658-188x250.jpg',
                'youtube_category_id' => null,
                'twitch_category_id' => '509658',
                'dailymotion_category_id' => null,
                'tags_json' => json_encode(['IRL']),
                'updated_at' => Carbon::now()->subHours(1)->toDateTimeString()

            ],
            [
                'slug' => 'minecraft',
                'name' => 'Minecraft',
                'description' => "Minecraft focuses on allowing the player to explore, interact with, and modify a dynamically-generated map made of one-cubic-meter-sized blocks. In addition to blocks, the environment features plants, mobs, and items. Some activities in the game include mining for ore, fighting hostile mobs, and crafting new blocks and tools by gathering various resources found in the game. The game's open-ended model allows players to create structures, creations, and artwork on various multiplayer servers or their single-player maps. Other features include redstone circuits for logic computations and remote actions, minecarts and tracks, and a mysterious underworld called the Nether. A designated but completely optional goal of the game is to travel to a dimension called the End, and defeat the ender dragon. ",
                'thumbnail_url' => 'https://static-cdn.jtvnw.net/ttv-boxart/27471_IGDB-188x250.jpg',
                'youtube_category_id' => null,
                'twitch_category_id' => '27471',
                'dailymotion_category_id' => null,
                'tags_json' => json_encode(['Survival','Open World']),
                'updated_at' => Carbon::now()->subHours(1)->toDateTimeString()
            ],[
                'slug' => 'valorant',
                'name' => 'Valorant',
                'description' => "VALORANT is a character-based 5v5 tactical shooter set on the global stage. Outwit, outplay, and outshine your competition with tactical abilities, precise gunplay, and adaptive teamwork. ",
                'thumbnail_url' => 'https://static-cdn.jtvnw.net/ttv-boxart/516575-188x250.jpg',
                'youtube_category_id' => null,
                'twitch_category_id' => '516575',
                'dailymotion_category_id' => null,
                'tags_json' => json_encode(['Shooter','FPS']),
                'updated_at' => Carbon::now()->subHours(1)->toDateTimeString()
            ],[
                'slug' => 'dota_2',
                'name' => 'Dota 2',
                'description' =>  "Dota 2 is a multiplayer online battle arena video game and the stand-alone sequel to the Defense of the Ancients (DotA) mod. With regular updates that ensure a constant evolution of gameplay, features, and heroes, Dota 2 has taken on a life of its own. ",
                'thumbnail_url' => 'https://static-cdn.jtvnw.net/ttv-boxart/29595-188x250.jpg',
                'youtube_category_id' => null,
                'twitch_category_id' => '29595',
                'dailymotion_category_id' => null,
                'tags_json' => json_encode(['Moba','Action']),
                'updated_at' => Carbon::now()->subHours(1)->toDateTimeString()
            ],[
                'slug' => 'GTA_V',
                'name' => 'GTA V',
                'description' => "Grand Theft Auto V is a vast open world game set in Los Santos, a sprawling sun-soaked metropolis struggling to stay afloat in an era of economic uncertainty and cheap reality TV. The game blends storytelling and gameplay in new ways as players repeatedly jump in and out of the lives of the game’s three lead characters, playing all sides of the game’s interwoven story. ",
                'thumbnail_url' => 'https://static-cdn.jtvnw.net/ttv-boxart/32982-188x250.jpg',
                'youtube_category_id' => null,
                'twitch_category_id' => '32982',
                'dailymotion_category_id' => null,
                'tags_json' => json_encode(['Shooter','Open World']),
                'updated_at' => Carbon::now()->subHours(1)->toDateTimeString()
            ],[
                'slug' => 'apex_legends',
                'name' => 'Apex Legends',
                'description' => "Conquer with character in Apex Legends, a free-to-play Hero shooter where legendary characters with powerful abilities team up to battle for fame & fortune on the fringes of the Frontier. Master an ever-growing roster of diverse Legends, deep tactical squad play and bold new innovations that go beyond the Battle Royale experience—all within a rugged world where anything goes. Welcome to the next evolution of Hero Shooter.",
                'thumbnail_url' => 'https://static-cdn.jtvnw.net/ttv-boxart/511224-188x250.jpg',
                'youtube_category_id' => null,
                'twitch_category_id' => '511224',
                'dailymotion_category_id' => null,
                'tags_json' => json_encode(['FPS','Shooter']),
                'updated_at' => Carbon::now()->subHours(1)->toDateTimeString()
            ],[
                'slug' => 'CSGO',
                'name' => 'Counter-Strike',
                'description' => "CS:GO is the fourth iteration of Valve's team-based modern-military first-person shooter that features new and updated versions of the classic CS content. While expanding the franchise, the game also introduces new gameplay modes, matchmaking and leader boards.",
                'thumbnail_url' => 'https://static-cdn.jtvnw.net/ttv-boxart/32399-188x250.jpg',
                'youtube_category_id' => null,
                'twitch_category_id' => '32399',
                'dailymotion_category_id' => null,
                'tags_json' => json_encode(['FPS','Shooter']),
                'updated_at' => Carbon::now()->subHours(1)->toDateTimeString()
            ],
        ];

        foreach($categories as $category) {
            $existingCategory = Category::where('slug', $category['slug'])->first();
            if (!$existingCategory) {
                Category::forceCreate(
                    [
                        'slug' => $category['slug'],
                        'name' => $category['name'],
                        'description' => $category['description'],
                        'thumbnail_url'=> $category['thumbnail_url'],
                        'youtube_category_id'=> $category['youtube_category_id'],
                        'twitch_category_id'=> $category['twitch_category_id'],
                        'dailymotion_category_id'=> $category['dailymotion_category_id'],
                        'tags_json'=> $category['tags_json'],
                        'updated_at'=> $category['updated_at']
                    ]
                );
            }
        }



    }
};
