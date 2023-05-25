<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\StreamModels\Stream;
use App\Models\StreamModels\StreamSource;
use Illuminate\Database\Seeder;

class StreamSeeder extends Seeder
{
    public function run(): void
    {
        //streams
        $stream1 = Stream::create([
            'slug' => rand(0, 999999),
            'creator_id' => 1,
            'preferred_source' => 'YouTube',
            'title' => 'ATT LIVE!',
            'description' => 'ohhhhh yyea',
            'viewers' => '300',
            'thumbnail_url' => 'dsfvdt4w3q234rwe',
            'is_live' => true,
            'karma' => '8',
            'category_id' => 1,
        ]);

        StreamSource::create([
            'stream_id' => $stream1->id,
            'source_name' => 'YouTube',
            'external_id' => 'jfKfPfyJRdk'
        ]);

        $categories = Category::all();
        $sources = ['YouTube', 'Twitch'];
        for ($i = 0; $i < 100; $i++) {
            $title = ucwords(implode(' ', $this->getRandomWords(4)));
            $description = ucfirst(implode(' ', $this->getRandomWords(20)));
            $tags = $this->getRandomWords(5);
            $thumbnail_id = rand(1, 1000);
            $thumbnail_url = "https://picsum.photos/id/{$thumbnail_id}/800/800";

            $stream = Stream::create([
                'slug' => rand(0, 999999),
                'creator_id' => 1,
                'preferred_source' => $sources[array_rand($sources)],
                'title' => $title,
                'description' => $description ,
                'category_id' => $categories->random()->id,
                'is_live' => true,
                'tags' => json_encode($tags),
                'visibility' => 'public',
                'viewers' => rand(0, 1000),
                'thumbnail_url' => $thumbnail_url,
                'live_viewer_count' => rand(0, 1),
            ]);

            $source_count = 2;

            for ($j = 0; $j < $source_count; $j++) {
                StreamSource::create([
                    'stream_id' => $stream->id,
                    'source_name' => $sources[$j],
                    'external_id' => $this->getRandomString(11),
                ]);
            }

        //Stream::factory()->count(100)->create();

        }

    }
    private function getRandomWords($count): array
    {
        $words = [
            'apple', 'banana', 'cherry', 'date', 'elderberry',
            'fig', 'grape', 'honeydew', 'indigo', 'jujube',
            'kiwi', 'lemon', 'mango', 'nectarine', 'orange',
            'papaya', 'quince', 'raspberry', 'strawberry', 'tangerine',
            'umbu', 'vanilla', 'watermelon', 'xylocarp', 'yellow watermelon',
            'zucchini'
        ];
        shuffle($words);
        return array_slice($words, 0, $count);
    }

    private function getRandomString($length): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $characters_length = strlen($characters);
        $random_string = '';
        for ($i = 0; $i < $length; $i++) {
            $random_string .= $characters[rand(0, $characters_length - 1)];
        }
        return $random_string;
    }
}
