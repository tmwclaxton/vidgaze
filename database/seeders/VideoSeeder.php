<?php

namespace Database\Seeders;

use App\Models\Video;
use App\Models\VideoSource;
use Illuminate\Database\Seeder;

class VideoSeeder extends Seeder
{
    public function run(): void
    {
        $vid1 = Video::create([
            'slug' => rand(0, 999999),
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

        //2nd video
        $vid2 = Video::create([
            'slug' => rand(0, 999999),
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
    }
}
