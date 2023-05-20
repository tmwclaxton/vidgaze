<?php

namespace Database\Factories\PodcastModels;

use App\Models\CreatorModels\Creator;
use App\Models\PodcastModels\Podcast;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class PodcastFactory extends Factory
{
    protected $model = Podcast::class;

    public function definition(): array
    {
        return [
            'rss_url' => $this->faker->url(),
            'category_id' => $this->faker->randomNumber(),
            'title' => $this->faker->word(),
            'description' => $this->faker->text(),
            'thumbnail_url' => 'https://picsum.photos/id/'. rand(0,33) .'/800/800',
            'like_count' => $this->faker->randomNumber(),
            'visibility' => $this->faker->randomElement(['public', 'unlisted', 'private']),

            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'creator_id' => Creator::factory(),
        ];
    }
}
