<?php

use App\Models\Union;
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
        Union::firstOrCreate([
            'slug' => 'vidgazers_unite',
            'name' => 'VidGazers Unite',
            'description' => 'A general purpose union run by VidGaze Ltd. for creators on VidGaze largely focused on improving monetization splits, promoting fairer demonetization and censorship rules.',
            'banner_url' => '/images/logo/vidgaze_banner_bg.png',
            'avatar_url' => '/images/logo/logo.png',
            'owner_id' => null,
            'member_count' => 0,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
//        Union::truncate();
    }
};
