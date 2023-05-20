<?php

namespace Database\Factories\CreatorModels;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CreatorModels\Creator>
 */
class CreatorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => $this->faker->word(1111),
            'user_id' => User::factory(),
            'slug' => random_int(0,10000000),
            'avatar_url' => 'https://picsum.photos/id/'. rand(0,33) .'/800/800',
            'banner_url' => 'https://picsum.photos/id/'. rand(0,33) .'/800/800',
            'coins' => rand(0,1000),
        ];
    }
}
