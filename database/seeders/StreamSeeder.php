<?php

namespace Database\Seeders;

use App\Models\Stream;
use App\Models\StreamSource;
use Illuminate\Database\Seeder;

class StreamSeeder extends Seeder
{
    public function run(): void
    {
        //streams
        $stream1 = Stream::create([
            'slug' => rand(0, 999999),
            'creator_id' => 1,
            'preferred_source' => 'youtube',
            'title' => 'ATT LIVE!',
            'description' => 'ohhhhh yyea',
            'viewers' => '300',
            'thumbnail_url' => 'dsfvdt4w3q234rwe',
            'karma' => '8',
            'category_id' => 1,
        ]);

        StreamSource::insert([[
            'stream_id' => $stream1->id,
            'source_name' => 'YouTube',
            'external_id' => 'asbt543w45tdfsewfret34',
        ],[
            'stream_id' => $stream1->id,
            'source_name' => 'Twitch',
            'external_id' => 'sdfgbtrew454ewDBRGEFW£4g5wq345',
        ]]);

        //StreamAward::create([
        //    'stream_id' => $stream1->id,
        //    'giver_id' => $creator3->id,
        //    'award_id' => $award2->id,
        //]);

        Stream::factory()->count(100)->create();

    }
}
