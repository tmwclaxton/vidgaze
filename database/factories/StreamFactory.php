<?php

namespace Database\Factories;

use App\Models\Creator;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Stream>
 */
class StreamFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $items = ['youtube','twitch'];

        return [
            'creator_id' => Creator::factory(),
            'title' => $this->faker->sentence(),
            'description' => '<p>'.$this->faker->paragraph(2, true). '</p>',
            'slug' => rand(1, 333000),
            'preferred_source' => $items[array_rand($items)],
            'is_live' => rand(0,1),
            'viewers' => rand(0,1000),
            'live_viewer_count' => rand(0,1),

            'category_id' => rand(1,8),
            'thumbnail_url' => 'https://picsum.photos/id/'. rand(0,33) .'/800/800',
        ];
    }
}
