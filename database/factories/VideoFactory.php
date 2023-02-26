<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Creator;
use App\Models\User;
use Delight\Random\Random;
use Illuminate\Database\Eloquent\Factories\Factory;


/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Video>
 */
class VideoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $items = ['youtube','dailymotion','vimeo','rumble','odysee'];
        return [
            'creator_id' => Creator::factory(),
            'title' => $this->faker->sentence(),
            'description' => '<p>'.$this->faker->paragraph(2, true). '</p>',
//            'body' => '<p>'.$this->faker->paragraph(10, true). '</p>',
            'slug' => Random::alphanumericHumanString(7),
            'duration' => random_int(1,5000),
            'preferred_source' => $items[array_rand($items)],
//            'category_id' => random_int(100,100000),
            'thumbnail_url' => 'https://picsum.photos/id/'. random_int(0,33) .'/800/800',
        ];
    }
}
