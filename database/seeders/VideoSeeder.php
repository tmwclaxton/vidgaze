<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\CreatorModels\Creator;
use App\Models\VideoModels\Video;
use App\Models\VideoModels\VideoSource;
use Illuminate\Database\Seeder;

class VideoSeeder extends Seeder
{
    public function run(): void
    {
        //array of 100 youtube video ids
        $youtube_ids = array_unique([
             'kffacxfA7G4', 'OPf0YbXqDm0', 'RgKAFK5djSk', '7PCkvCPvDXk',
            '6Dh-RL__uN4', '0JG6R4cRwAI', 'hMnk7lh9M3o', 'Xl8t8P8D7EU', 'QGJuMBdaqIw',
            '3tmd-ClpJxA', 'dQw4w9WgXcQ', 'n6wRGNIPjhI', 'o4oyDpCnPE0', 'u9Bfj8C6vAw',
            '0Aa_zHEEa1U', 'YlUKcNNmywk', 'tPEE9ZwTmy0', '8UVNT4wvIGY', 'Bm8rz-llMhE',
            'vTIIMJ9tUc8', 'F57P9C4SAW4', 'JGwWNGJdvx8', '9vMh9f41pqE', 'BB0DU4DoPP4',
            'LW3n2wzeCSc', 'YQHsXMglC9A', '92cwKCU8Z5c', 'gXbjM-5eU9g', 'NG1qooBzE_o',
            'MV_3Dpw-BRY', 'e-ORhEE9VVg', '1kIsylLeHHU', 'Xl8t8P8D7EU', 'hHUbLv4ThOo',
            'o-v9MIK_YTk', 'B7yAI4F7MTQ', 'QJO3ROT-A4E', '9bZkp7q19f0', 'kXYiU_JCYtU',
            'TKmGU5yGcDE', 'DjQd4EzW8EI', 'DK_0jXPuIr0', 'vBbuebI7lT0', 'Nt8a4sDOFlc',
            'O-zpOMYRi0w', 'L-4l98cJNTI', '1vlQ2L-NQK8', 'JGwWNGJdvx8', 'MIaEx1sBxdE',
            'e-ORhEE9VVg', 'FtutLA63Cp8', 'Fm5iP0S1z9w', '6Dh-RL__uN4', 'sEwM6ERq0gc',
            'ru0K8uYEZWw', 'XP9pnSX6fsw', 'XTfjZ4h9J78', 'dQw4w9WgXcQ',
            'fRh_vgS2dFE', 'Fr8HKRTavMw', '3mbBbFH9fAg', '2Vv-BfVoq4g', 'UaZD8J_yq9o',
            'bKDdT_nyP54', 'N9wXM3b4_4Q', 'kXYiU_JCYtU'
        ]);

        $vimeo_ids =  array_unique([
            '264716931', '262744826', '266736509', '255683337', '269225540',
            '261856030', '263595174', '268847257', '269936489', '265532149',
            '262835758', '267042774', '263108586', '261919577', '264716931',
            '262744826', '266736509', '255683337', '269225540', '261856030',
            '263595174', '268847257', '269936489', '265532149', '262835758',
            '267042774', '263108586', '261919577', '264716931', '262744826',
            '266736509', '255683337', '269225540', '261856030', '263595174',
            '268847257', '269936489', '265532149', '262835758', '267042774',
            '263108586', '261919577', '264716931', '262744826', '266736509',
            '255683337', '269225540', '261856030', '263595174', '268847257',
            '269936489', '265532149', '262835758', '267042774', '263108586',
            '261919577', '264716931', '262744826', '266736509', '255683337',
            '269225540', '261856030', '263595174', '268847257', '269936489',
            '265532149', '262835758', '267042774', '263108586', '261919577',
            '264716931', '262744826', '266736509', '255683337', '269225540',
            '261856030', '263595174', '268847257', '269936489', '265532149',
            '262835758', '267042774', '263108586', '261919577', '264716931',
            '262744826', '266736509', '255683337', '269225540', '261856030',
            '263595174', '268847257', '269936489', '265532149', '262835758',
            '267042774', '263108586', '261919577'
        ]);

        $dailymotion_ids =  array_unique([
            'x26ezr', 'x27sm89', 'x23z7ut', 'x22g6g', 'x25y3co',
            'x21vvef', 'x23d6jo', 'x27x8fu', 'x29yvbu', 'x27eeeg',
            'x23bjo1', 'x27b3fi', 'x24ue15', 'x23db2p', 'x26ezr',
            'x27sm89', 'x23z7ut', 'x22g6g', 'x25y3co', 'x21vvef',
            'x23d6jo', 'x27x8fu', 'x29yvbu', 'x27eeeg', 'x23bjo1',
            'x27b3fi', 'x24ue15', 'x23db2p', 'x26ezr', 'x27sm89',
            'x23z7ut', 'x22g6g', 'x25y3co', 'x21vvef', 'x23d6jo',
            'x27x8fu', 'x29yvbu', 'x27eeeg', 'x23bjo1', 'x27b3fi',
            'x24ue15', 'x23db2p', 'x26ezr', 'x27sm89', 'x23z7ut',
            'x22g6g', 'x25y3co', 'x21vvef', 'x23d6jo', 'x27x8fu',
            'x29yvbu', 'x27eeeg', 'x23bjo1', 'x27b3fi', 'x24ue15',
            'x23db2p', 'x26ezr', 'x27sm89', 'x23z7ut', 'x22g6g',
            'x25y3co', 'x21vvef', 'x23d6jo', 'x27x8fu', 'x29yvbu',
            'x27eeeg', 'x23bjo1', 'x27b3fi', 'x24ue15', 'x23db2p',
            'x26ezr', 'x27sm89', 'x23z7ut', 'x22g6g', 'x25y3co',
            'x21vvef', 'x23d6jo', 'x27x8fu', 'x29yvbu', 'x27eeeg',
            'x23bjo1', 'x27b3fi', 'x24ue15', 'x23db2p', 'x26ezr',
            'x27sm89', 'x23z7ut', 'x22g6g', 'x25y3co', 'x21vvef',
            'x23d6jo', 'x27x8fu', 'x29yvbu', 'x27eeeg', 'x23bjo1',
            'x27b3fi', 'x24ue15', 'x23db2p'
        ]);


        $vid1 = Video::create([
            'slug' => rand(0, 99911999),
            'creator_id' => 1,
            'preferred_source' => 'YouTube',
            'title' => 'Communism and Noah\'s Flood',
            'description' => 'Could we really have predicted the horrifying outcome of communism from a story we tell to children?',
            'karma' => '32',
            'duration' => '2700',
            'category_id' => 1,
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
        VideoSource::create([
            'video_id' => $vid1->id,
            'source_name' => 'Vimeo',
            'external_id' => 'D26kGN456NqjhU'
        ]);
        //
        ////2nd video
        $vid2 = Video::create([
            'slug' => rand(0, 99911999),
            'creator_id' => 1,
            'preferred_source' => 'YouTube',
            'title' => 'The Truth About the Moon Landing',
            'description' => 'The truth about the moon landing is that it was faked.',
            'karma' => '32',
            'duration' => '2700',
            'category_id' => 1,
            'tags' => json_encode(['Moon Landing', 'Conspiracy']),
            'visibility' => 'public',
            'like_count' => 5,
            'thumbnail_url' => 'https://picsum.photos/id/33/800/800',
            'time_published' => now(),
        ]);
        VideoSource::create([
            'video_id' => $vid2->id,
            'source_name' => 'YouTube',
            'external_id' => 'D26U'
        ]);
        $creators = Creator::factory(50)->create();
        $categories = Category::all();
        $sources = ['YouTube', 'Vimeo', 'Dailymotion'];
        for ($i = 0; $i < 13; $i++) {
            $title = ucwords(implode(' ', $this->getRandomWords(4)));
            $description = ucfirst(implode(' ', $this->getRandomWords(20)));
            $tags = $this->getRandomWords(5);
            $thumbnail_id = rand(1, 1000);
            $thumbnail_url = "https://picsum.photos/id/{$thumbnail_id}/800/800";

            $video = Video::create([
                'slug' => rand(0, 999999),
                'creator_id' => $creators->random()->id,
                'preferred_source' => $sources[array_rand($sources)],
                'title' => $title,
                'description' => $description,
                'karma' => rand(0, 100),
                'duration' => rand(0, 200),
                'category_id' => $categories->random()->id,
                'tags' => json_encode($tags),
                'visibility' => 'public',
                'like_count' => rand(0, 50),
                'dislike_count' => rand(0, 50),
                'view_count' => rand(0, 1000),
                'thumbnail_url' => $thumbnail_url,
                'time_published' => now(),
                'live_viewer_count' => rand(0, 1),
            ]);

            $source_count = 3;
            $platforms = ['YouTube', 'Vimeo', 'Dailymotion'];

            for ($j = 0; $j < $source_count; $j++ ) {
                $platform = $platforms[$j];
                $external_id = '';

                if ($platform === 'YouTube') {
                    $external_id = $youtube_ids[$i];
                } elseif ($platform === 'Vimeo') {
                    $external_id = $vimeo_ids[$i];
                } elseif ($platform === 'Dailymotion') {
                    $external_id = $dailymotion_ids[$i];
                }

                VideoSource::create([
                    'video_id' => $video->id,
                    'source_name' => $platform,
                    'external_id' => $external_id,
                ]);
            }
        }
        //Video::factory()->count(100)->create();

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

    //private function getRandomString($length): string
    //{
    //    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    //    $characters_length = strlen($characters);
    //    $random_string = '';
    //    for ($i = 0; $i < $length; $i++) {
    //        $random_string .= $characters[rand(0, $characters_length - 1)];
    //    }
    //    return $random_string;
    //}


}
