<?php

namespace Database\Factories;

use App\Models\CreatorModels\Creator;
use App\Models\VideoModels\Video;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CommentModels\Comment>
 */
class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'body' => $this->faker->paragraph(),
            'post_id' => Video::factory(),
            'creator_id' => Creator::factory()
        ];
    }
}
