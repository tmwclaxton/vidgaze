<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Video;
use App\Models\VideoSource;
use Illuminate\Database\Seeder;

class VideoSeeder extends Seeder
{
    public function run(): void
    {
        //$vid1 = Video::create([
        //    'slug' => rand(0, 999999),
        //    'creator_id' => 1,
        //    'preferred_source' => 'YouTube',
        //    'title' => 'Communism and Noah\'s Flood',
        //    'description' => 'Could we really have predicted the horrifying outcome of communism from a story we tell to children?',
        //    'karma' => '32',
        //    'duration' => '2700',
        //    'category_id' => 1,
        //    'tags' => json_encode(['Communism', 'Noah\'s Flood']),
        //    'visibility' => 'public',
        //    'like_count' => 5,
        //    'thumbnail_url' => 'https://picsum.photos/id/33/800/800',
        //    'time_published' => now(),
        //]);
        //VideoSource::create([
        //    'video_id' => $vid1->id,
        //    'source_name' => 'YouTube',
        //    'external_id' => 'D26kGNNqjhU'
        //]);
        //VideoSource::create([
        //    'video_id' => $vid1->id,
        //    'source_name' => 'Vimeo',
        //    'external_id' => 'D26kGN456NqjhU'
        //]);
        //
        ////2nd video
        //$vid2 = Video::create([
        //    'slug' => rand(0, 999999),
        //    'creator_id' => 1,
        //    'preferred_source' => 'YouTube',
        //    'title' => 'The Truth About the Moon Landing',
        //    'description' => 'The truth about the moon landing is that it was faked.',
        //    'karma' => '32',
        //    'duration' => '2700',
        //    'category_id' => 1,
        //    'tags' => json_encode(['Moon Landing', 'Conspiracy']),
        //    'visibility' => 'public',
        //    'like_count' => 5,
        //    'thumbnail_url' => 'https://picsum.photos/id/33/800/800',
        //    'time_published' => now(),
        //]);
        //VideoSource::create([
        //    'video_id' => $vid2->id,
        //    'source_name' => 'YouTube',
        //    'external_id' => 'D26U'
        //]);

        $categories = Category::all();
        $sources = ['YouTube', 'Vimeo', 'Dailymotion'];

        for ($i = 0; $i < 200; $i++) {
            $title = ucwords(implode(' ', $this->getRandomWords(4)));
            $description = ucfirst(implode(' ', $this->getRandomWords(20)));
            $tags = $this->getRandomWords(5);
            $thumbnail_id = rand(1, 1000);
            $thumbnail_url = "https://picsum.photos/id/{$thumbnail_id}/800/800";

            $video = Video::create([
                'slug' => rand(0, 999999),
                'creator_id' => 1,
                'preferred_source' => 'YouTube',
                'title' => $title,
                'description' => $description,
                'karma' => rand(0, 100),
                'duration' => rand(600, 1800),
                'category_id' => $categories->random()->id,
                'tags' => json_encode($tags),
                'visibility' => 'public',
                'like_count' => rand(0, 50),
                'thumbnail_url' => $thumbnail_url,
                'time_published' => now(),
            ]);

            $source_count = rand(1, 3);

            for ($j = 0; $j < $source_count; $j++) {
                VideoSource::create([
                    'video_id' => $video->id,
                    'source_name' => $sources[$j],
                    'external_id' => $this->getRandomString(11),
                ]);
            }
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
