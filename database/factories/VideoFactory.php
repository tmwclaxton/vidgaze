<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Creator;
use App\Models\User;
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
            'slug' => rand(1, 333000),
            'duration' => rand(1, 60),
            'preferred_source' => $items[array_rand($items)],
            'thumbnail_url' => 'https://picsum.photos/id/'. rand(0,33) .'/800/800',
        ];
    }
}
