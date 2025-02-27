<?php

use App\Models\Award;
use App\Models\CreatorModels\Creator;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // create 2 users
        $users = [
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'johndoe@gmail.com',
                'password' => bcrypt('password')
            ],
            [
                'first_name' => 'Elon',
                'last_name' => 'Sucks',
                'email' => 'elonsucks@cock.com',
                'password' => bcrypt('password')
            ]
        ];

        foreach ($users as $user) {
            User::create($user);
            // create a creator model for each user
            Creator::create([
                'user_id' => User::where('email', $user['email'])->first()->id,
                'slug' => User::where('email', $user['email'])->first()->first_name,
                'name' => User::where('email', $user['email'])->first()->first_name,
                'avatar_url' => 'https://via.placeholder.com/150',
                'banner_url' => 'https://via.placeholder.com/150',
                'karma' => 0,
                'subscriber_count' => 0,
                'comment_count' => 0,
                'bio' => null,
                'region' => 'US',
                'coins' => 0,
                'language' => 'en',
                'category_id' => 1
            ]);
        }



    }
};
