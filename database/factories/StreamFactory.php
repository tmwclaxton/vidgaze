<?php

namespace Database\Factories;

use App\Models\Creator;
use Delight\Random\Random;
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
            'slug' => Random::alphanumericHumanString(7),
            'preferred_source' => $items[array_rand($items)],

            'category_id' => random_int(1,8),
            'thumbnail_url' => 'https://picsum.photos/id/'. random_int(0,33) .'/800/800',
        ];
    }
}
