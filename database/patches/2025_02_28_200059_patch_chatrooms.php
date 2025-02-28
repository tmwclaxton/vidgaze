<?php

use App\Models\ChatRoom;
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
        $chatrooms = [
            [
                'name' => 'General',
                'description' => 'Talk about anything you want here'
            ],
            [
                'name' => 'Direction of VidGaze Platform',
                'description' => 'Where do you want this project to go?'
            ],
            [
                'name' => 'Freedom Tech',
                'description' => 'This place is for talking about decentralised and open source technology'
            ],
            [
                'name' => 'Wealth Inequality',
                'description' => 'Chatroom for Wealth Inequality'
            ],
            [
                'name' => 'Pol',
                'description' => 'Welcome to the Pol chatroom'
            ],

        ];

        foreach($chatrooms as $chatroom) {
            ChatRoom::create($chatroom);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
