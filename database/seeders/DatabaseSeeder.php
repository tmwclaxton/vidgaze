<?php

namespace Database\Seeders;

use App\Enums\Platforms;
use App\Models\Award;
use App\Models\Category;
use App\Models\channelDisinterest;
use App\Models\Comment;
use App\Models\CommentAward;
use App\Models\CommentInteraction;
use App\Models\Creator;
use App\Models\CreatorSource;
use App\Models\Payment;
use App\Models\Playlist;
use App\Models\PlaylistVideo;
use App\Models\Podcast;
use App\Models\Product;
use App\Models\Stream;
use App\Models\StreamAward;
use App\Models\StreamSource;
use App\Models\Subscription;
use App\Models\Union;
use App\Models\UnionMembership;
use App\Models\User;
use App\Models\Video;
use App\Models\VideoAward;
use App\Models\videoDisinterest;
use App\Models\videoReport;
use App\Models\VideoSource;
use App\Models\VideoViewInfos;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

//use App\Models\CommentDislike;
//use App\Models\CommentLike;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();
        Video::truncate();
        Stream::truncate();
        Podcast::truncate();
        VideoSource::truncate();
        StreamSource::truncate();

        $this->call(PodcastSeeder::class);
        $this->call(VideoSeeder::class);
        $this->call(StreamSeeder::class);


        ////////////////// end wipe //////////////////

        // use individual seeders to populate database not this
        if (true === false) {

            //////// wipe database for re-seeding ////////
            Schema::disableForeignKeyConstraints();

            //Alphabetical
            Award::truncate();
            Category::truncate();
            Comment::truncate();
            CommentAward::truncate();
            CommentInteraction::truncate();
            //CommentLike::truncate();
            Creator::truncate();
            Playlist::truncate();
            PlaylistVideo::truncate();
            Stream::truncate();
            StreamAward::truncate();
            Subscription::truncate();
            Union::truncate();
            UnionMembership::truncate();
            User::truncate();
            Video::truncate();
            VideoAward::truncate();
            VideoViewInfos::truncate();
            Product::truncate();
            Payment::truncate();
            CreatorSource::truncate();
            channelDisinterest::truncate();
            videoDisinterest::truncate();
            videoReport::truncate();
            Schema::enableForeignKeyConstraints();

            //(new CategorySeeder())->run();

            $products = [
                [
                    //   test             'price_id'=>'price_1LgCQbAe7hH7XTwzXnqkkXoG',
                    'price_id' => 'price_1LgdmeAe7hH7XTwzzoyACnwt',
                    'image_url' => '/images/vidcoins/ArmfulOfCoins.png',
                    'description' => 'Give 2 Platinum, or 5 Gold, or 10 Silver Awards.',
                    'name' => '5,000 VidCoins',
                    'price' => 599
                ],
                [
                    //   test             'price_id'=>'price_1LgSWFAe7hH7XTwzoakAGX9a',
                    'price_id' => 'price_1LgdodAe7hH7XTwz97hyZqcG',
                    'image_url' => '/images/vidcoins/DraggingSack.png',
                    'description' => 'Give 12 Platinum, or 25 Gold, or 50 Silver Awards.',
                    'name' => '25,000 VidCoins',
                    'price' => 2499
                ],
                [
                    //   test             'price_id'=>'price_1LgSlLAe7hH7XTwzMXBu8lOX',
                    'price_id' => 'price_1LgdpDAe7hH7XTwzuXViqe2F',
                    'image_url' => '/images/vidcoins/PushingCrate.png',
                    'description' => 'Give 50 Platinum, or 100 Gold, or 200 Silver Awards.',
                    'name' => '100,000 VidCoins',
                    'price' => 7999
                ],
            ];
            Product::insert($products);


            $awardFilePath = "/images/awards/";
            $awardFileType = ".png";
            //All awards
            $award1 = Award::create([
                'name' => 'Gold',
                'description' => 'Reserved for only the best posts!',
                'icon_url' => $awardFilePath . 'GoldAward' . '.png',
                'coin_price' => '1000',
                'gifted_coins' => '40',
            ]);

            $award2 = Award::create([
                'name' => 'Silver',
                'description' => 'Behold the Silver Medal',
                'icon_url' => $awardFilePath . 'SilverAward' . $awardFileType,
                'coin_price' => '500',
                'gifted_coins' => '0',
            ]);
            $awards = [
                [
                    'name' => 'Platinum',
                    'description' => 'Unironically better than the Gold Award',
                    'icon_url' => $awardFilePath . 'DiamondPlatinumAward' . $awardFileType,
                    'coin_price' => '2000',
                    'gifted_coins' => '0',
                ], [
                    'name' => 'Supernova',
                    'description' => 'Star go boom...',
                    'icon_url' => $awardFilePath . 'SupernovaAward' . $awardFileType,
                    'coin_price' => '7500',
                    'gifted_coins' => '0',
                ], [
                    'name' => 'Flaming Heart',
                    'description' => 'My heart will go on...',
                    'icon_url' => $awardFilePath . 'FlamingHeart' . $awardFileType,
                    'coin_price' => '150',
                    'gifted_coins' => '0',
                ], [
                    'name' => 'Point Upwards',
                    'description' => 'This one right here.',
                    'icon_url' => $awardFilePath . 'Upvote' . $awardFileType,
                    'coin_price' => '50',
                    'gifted_coins' => '0',
                ], [
                    'name' => 'Onion',
                    'description' => 'Just an onion',
                    'icon_url' => $awardFilePath . 'OnionBase' . $awardFileType,
                    'coin_price' => '300',
                    'gifted_coins' => '0',
                ],
                [
                    'name' => 'Angry Onion',
                    'description' => 'He\'s pissed off.  He ain\'t messing around.',
                    'icon_url' => $awardFilePath . 'OnionFiredUp' . $awardFileType,
                    'coin_price' => '300',
                    'gifted_coins' => '0',
                ],
                [
                    'name' => 'OMG AN ONION',
                    'description' => 'IS LIFE EVEN REAL!!!',
                    'icon_url' => $awardFilePath . 'OnionWOW' . $awardFileType,
                    'coin_price' => '300',
                    'gifted_coins' => '0',
                ],
                [
                    'name' => 'Laughter',
                    'description' => 'Laughing is healthy bro',
                    'icon_url' => $awardFilePath . 'MascotLaughing' . $awardFileType,
                    'coin_price' => '250',
                    'gifted_coins' => '0',
                ], [
                    'name' => 'In love',
                    'description' => 'I\'ll find someone...',
                    'icon_url' => $awardFilePath . 'MascotHearteyes' . $awardFileType,
                    'coin_price' => '250',
                    'gifted_coins' => '0',
                ], [
                    'name' => 'Let it out',
                    'description' => 'Don\'t keep it bottled in',
                    'icon_url' => $awardFilePath . 'MascotCrying' . $awardFileType,
                    'coin_price' => '250',
                    'gifted_coins' => '0',
                ], [//
                    'name' => 'hugz',
                    'description' => 'I demand hugs now...',
                    'icon_url' => $awardFilePath . 'MascotHuggingAlien' . $awardFileType,
                    'coin_price' => '10',
                    'gifted_coins' => '0',
                ], [
                    'name' => 'Rocket go brrrrr',
                    'description' => 'Boldly go where we haven\'t been in a long, long time.',
                    'icon_url' => $awardFilePath . 'Rocket' . $awardFileType,
                    'coin_price' => '750',
                    'gifted_coins' => '0',
                ], [
                    'name' => 'Space Cat',
                    'description' => 'vibin',
                    'icon_url' => $awardFilePath . 'SpaceCat' . $awardFileType,
                    'coin_price' => '350',
                    'gifted_coins' => '0',
                ], [
                    'name' => 'Big rock go boom!',
                    'description' => 'Don\'t look up...',
                    'icon_url' => $awardFilePath . 'Meteor' . $awardFileType,
                    'coin_price' => '500',
                    'gifted_coins' => '0',
                ]];
            Award::insert($awards);


            $cat1 = Category::create([
                'slug' => 'league_of_legends',
                'name' => 'League of Legends',
                'description' => "League of Legends is a fast-paced, competitive online game that blends the speed and intensity of an RTS with RPG elements. Two teams of powerful champions, each with a unique design and playstyle, battle head-to-head across multiple battlefields and game modes. With an ever-expanding roster of champions, frequent updates and a thriving tournament scene, League of Legends offers endless replayability for players of every skill level. ",
                'thumbnail_url' => 'https://static-cdn.jtvnw.net/ttv-boxart/21779-188x250.jpg',
                'youtube_category_id' => '',
                'twitch_category_id' => '21779',
                'dailymotion_category_id' => '',
                'tags_json' => json_encode(['MOBA', 'Action']),
            ]);

            $cat2 = Category::create([
                'slug' => 'just_chatting',
                'name' => 'Just Chatting',
                'description' => "A category for streams that lack gameplay or a structured presentation, but rather are focused on a conversation between the streamer and other streamers or between the streamer and the viewers.",
                'thumbnail_url' => 'https://static-cdn.jtvnw.net/ttv-boxart/509658-188x250.jpg',
                'youtube_category_id' => null,
                'twitch_category_id' => '509658',
                'dailymotion_category_id' => null,
                'tags_json' => json_encode(['IRL']),
            ]);
            $categories = [
                [
                    'slug' => 'minecraft',
                    'name' => 'Minecraft',
                    'description' => "Minecraft focuses on allowing the player to explore, interact with, and modify a dynamically-generated map made of one-cubic-meter-sized blocks. In addition to blocks, the environment features plants, mobs, and items. Some activities in the game include mining for ore, fighting hostile mobs, and crafting new blocks and tools by gathering various resources found in the game. The game's open-ended model allows players to create structures, creations, and artwork on various multiplayer servers or their single-player maps. Other features include redstone circuits for logic computations and remote actions, minecarts and tracks, and a mysterious underworld called the Nether. A designated but completely optional goal of the game is to travel to a dimension called the End, and defeat the ender dragon. ",
                    'thumbnail_url' => 'https://static-cdn.jtvnw.net/ttv-boxart/27471_IGDB-188x250.jpg',
                    'youtube_category_id' => null,
                    'twitch_category_id' => '27471',
                    'dailymotion_category_id' => null,
                    'tags_json' => json_encode(['Survival', 'Open World']),
                    'updated_at' => Carbon::now()->subHours(1)->toDateTimeString()
                ], [
                    'slug' => 'valorant',
                    'name' => 'Valorant',
                    'description' => "VALORANT is a character-based 5v5 tactical shooter set on the global stage. Outwit, outplay, and outshine your competition with tactical abilities, precise gunplay, and adaptive teamwork. ",
                    'thumbnail_url' => 'https://static-cdn.jtvnw.net/ttv-boxart/516575-188x250.jpg',
                    'youtube_category_id' => null,
                    'twitch_category_id' => '516575',
                    'dailymotion_category_id' => null,
                    'tags_json' => json_encode(['Shooter', 'FPS']),
                    'updated_at' => Carbon::now()->subHours(1)->toDateTimeString()
                ], [
                    'slug' => 'dota_2',
                    'name' => 'Dota 2',
                    'description' => "Dota 2 is a multiplayer online battle arena video game and the stand-alone sequel to the Defense of the Ancients (DotA) mod. With regular updates that ensure a constant evolution of gameplay, features, and heroes, Dota 2 has taken on a life of its own. ",
                    'thumbnail_url' => 'https://static-cdn.jtvnw.net/ttv-boxart/29595-188x250.jpg',
                    'youtube_category_id' => null,
                    'twitch_category_id' => '29595',
                    'dailymotion_category_id' => null,
                    'tags_json' => json_encode(['Moba', 'Action']),
                    'updated_at' => Carbon::now()->subHours(1)->toDateTimeString()
                ], [
                    'slug' => 'GTA_V',
                    'name' => 'GTA V',
                    'description' => "Grand Theft Auto V is a vast open world game set in Los Santos, a sprawling sun-soaked metropolis struggling to stay afloat in an era of economic uncertainty and cheap reality TV. The game blends storytelling and gameplay in new ways as players repeatedly jump in and out of the lives of the game’s three lead characters, playing all sides of the game’s interwoven story. ",
                    'thumbnail_url' => 'https://static-cdn.jtvnw.net/ttv-boxart/32982-188x250.jpg',
                    'youtube_category_id' => null,
                    'twitch_category_id' => '32982',
                    'dailymotion_category_id' => null,
                    'tags_json' => json_encode(['Shooter', 'Open World']),
                    'updated_at' => Carbon::now()->subHours(1)->toDateTimeString()
                ], [
                    'slug' => 'apex_legends',
                    'name' => 'Apex Legends',
                    'description' => "Conquer with character in Apex Legends, a free-to-play Hero shooter where legendary characters with powerful abilities team up to battle for fame & fortune on the fringes of the Frontier. Master an ever-growing roster of diverse Legends, deep tactical squad play and bold new innovations that go beyond the Battle Royale experience—all within a rugged world where anything goes. Welcome to the next evolution of Hero Shooter.",
                    'thumbnail_url' => 'https://static-cdn.jtvnw.net/ttv-boxart/511224-188x250.jpg',
                    'youtube_category_id' => null,
                    'twitch_category_id' => '511224',
                    'dailymotion_category_id' => null,
                    'tags_json' => json_encode(['FPS', 'Shooter']),
                    'updated_at' => Carbon::now()->subHours(1)->toDateTimeString()
                ], [
                    'slug' => 'CSGO',
                    'name' => 'Counter-Strike',
                    'description' => "CS:GO is the fourth iteration of Valve's team-based modern-military first-person shooter that features new and updated versions of the classic CS content. While expanding the franchise, the game also introduces new gameplay modes, matchmaking and leader boards.",
                    'thumbnail_url' => 'https://static-cdn.jtvnw.net/ttv-boxart/32399-188x250.jpg',
                    'youtube_category_id' => null,
                    'twitch_category_id' => '32399',
                    'dailymotion_category_id' => null,
                    'tags_json' => json_encode(['FPS', 'Shooter']),
                    'updated_at' => Carbon::now()->subHours(1)->toDateTimeString()
                ],
            ];
            Category::insert($categories);

            $union2 = Union::create([
                'slug' => 'vidgazers_unite',
                'name' => 'VidGazers Unite',
                'description' => 'A general purpose union run by VidGaze Ltd. for creators on VidGaze largely focused on improving monetization splits, promoting fairer demonetization and censorship rules.',
                'banner_url' => '/images/logo/vidgaze_banner_bg.png',
                'avatar_url' => '/images/logo/logo.png',
                'owner_id' => null,
                'member_count' => 0,
            ]);


            if (app()->environment() !== 'production') {
                $user1 = User::create([
                    'first_name' => 'Joshua',
                    'last_name' => 'Young',
                    'email' => 'josh@vidgaze.tv',
                    'DOB' => '2002-06-27',
                    'password' => bcrypt('password'),
                ]);
                $user2 = User::create([
                    'first_name' => 'Toby',
                    'last_name' => 'Claxton',
                    'email' => 'toby@vidgaze.tv',
                    'DOB' => '2003-04-23',
                    'password' => bcrypt('password'),
                ]);

                $creator1 = Creator::create([
                    'slug' => 'yt_UCpY8z5jdC8QQ_zyHxdKJoKw',
                    'user_id' => $user1->id,
                    'name' => 'Accessory To Thought',
                    'avatar_url' => 'https://yt3.ggpht.com/OOgRgyC3BKuVcrFXWBt7-2H_e01YWKjOp32fWeuhiOqtlGobDEv4QZ3I4q5_AV3ekFS-OPfklLI=s176-c-k-c0x00ffffff-no-rj',
                    'banner_url' => 'https://yt3.ggpht.com/t4uitEqCYaQlj89MPeJMynNpby8pDdE_qBReIY-t9a5KVaeoeVKGpUnfUGuBDgk8jtcsCGPwpA=w1060-fcrop64=1,00005a57ffffa5a8-k-c0xffffffff-no-nd-rj',
                    'karma' => 69000,
                    'bio' => json_encode('your catalyst to deeper thinking'),
                    'region' => 'ir',
                    'coins' => '300',
                    'category_id' => $cat2->id,
                ]);

                CreatorSource::create([
                    'creator_id' => $creator1->id,
                    'source_name' => Platforms::YouTube->name,
                    'external_channel_id' => 'UCpY8z5jdC8QQ_zyHxdKJoKw',
                ]);


                $creator3 = Creator::create([
                    'slug' => 'dsfgthy654ewrt',
                    'user_id' => $user2->id,
                    'name' => 'TobyClaxton',
                    'karma' => 50000,
                    'bio' => json_encode("China's population is going to zero man"),
                    'region' => 'de',
                    'coins' => '3025',
                    'category_id' => $cat1->id,
                ]);
                $vid1 = Video::create([
                    'slug' => substr(strtoupper(sha1(time())), 0, 16),
                    'creator_id' => $creator1->id,
                    'preferred_source' => 'YouTube',
                    'title' => 'Communism and Noah\'s Flood',
                    'description' => 'Could we really have predicted the horrifying outcome of communism from a story we tell to children?',
                    'karma' => '32',
                    'duration' => '2700',
                    'category_id' => $cat2->id,
                    'tags' => json_encode(['Communism', 'Noah\'s Flood']),
                    'visibility' => 'public',
                    'like_count' => 5,
                    'thumbnail_url' => 'https://picsum.photos/id/33/800/800',
                    'time_published' => now(),
                ]);
                VideoSource::create([
                    'video_id' => $vid1->id,
                    'source_name' => 'YouTube',
                    'external_id' => 'D26kGNNqjhU'
                ]);
                $com1 = Comment::create([
                    'creator_id' => $creator3->id,
                    'video_id' => $vid1->id,
                    'parent_comment_id' => null,
                    'body' => 'sick video bud!',
                ]);

                $reply1 = Comment::create([
                    'creator_id' => $creator1->id,
                    'video_id' => $vid1->id,
                    'parent_comment_id' => $com1->id,
                    'body' => 'thanks bro',
                ]);

                $replyreply1 = Comment::create([
                    'creator_id' => $creator3->id,
                    'video_id' => $vid1->id,
                    'parent_comment_id' => $reply1->id,
                    'body' => 'no worries bro',
                ]);

                CommentInteraction::create([
                    'comment_id' => $com1->id,
                    'creator_id' => $creator1->id,
                    'liked' => 'dislike'
                ]);

                CommentAward::create([
                    'comment_id' => $com1->id,
                    'giver_id' => $creator1->id,
                    'award_id' => $award1->id,
                ]);

                VideoAward::create([
                    'video_id' => $vid1->id,
                    'giver_id' => $creator3->id,
                    'award_id' => $award1->id,
                ]);

                //        $stream1 = Stream::create([
                //            'slug' => 'sdcfvtg543wfertby23f4r',
                //            'creator_id' => $creator1->id,
                //            'preferred_source' => 'youtube',
                //            'title' => 'ATT LIVE!',
                //            'description' => 'ohhhhh yyea',
                //            'viewers' => '300',
                //            'thumbnail_url' => 'dsfvdt4w3q234rwe',
                //            'karma' => '8',
                //            'category_id' => $cat2->id,
                //        ]);
                //
                //        StreamSource::insert([[
                //            'stream_id' => $stream1->id,
                //            'source_name' => 'YouTube',
                //            'external_id' => 'asbt543w45tdfsewfret34',
                //        ],[
                //            'stream_id' => $stream1->id,
                //            'source_name' => 'Twitch',
                //            'external_id' => 'sdfgbtrew454ewDBRGEFW£4g5wq345',
                //        ]]);
                //
                //        StreamAward::create([
                //            'stream_id' => $stream1->id,
                //            'giver_id' => $creator3->id,
                //            'award_id' => $award2->id,
                //        ]);

                Subscription::create([
                    'subscriber_id' => $creator3->id,
                    'creator_id' => $creator1->id,
                ]);

                $playlist1 = Playlist::create([
                    'creator_id' => $creator1->id,
                    'slug' => substr(strtoupper(sha1(time())), 0, 16),
                    'name' => 'Cool Vids',
                    'server_made' => false,
                    'visibility' => 'public',
                    'list' => '[1,2,3,4]',
                    'description' => 'only the coolest of vids on VidGaze',
                    'recent_video_image' => 'https://i.ytimg.com/vi/5WXo1aFb8MY/hq720.jpg?sqp=-oaymwEcCNAFEJQDSFXyq4qpAw4IARUAAIhCGAFwAcABBg==&rs=AOn4CLDxrv5llwuO_eQdhCtqm193roP0pw',
                    'video_count' => 1,

                ]);

                PlaylistVideo::create([
                    'playlist_id' => $playlist1->id,
                    'video_id' => $vid1->id,
                ]);
            }
        }

    }
}
