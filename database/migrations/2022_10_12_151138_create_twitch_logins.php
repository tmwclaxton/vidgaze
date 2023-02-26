<?php

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
        if (!Schema::hasTable('twitch_logins')) {
            // your migrations
            Schema::create('twitch_logins', function (Blueprint $table) {
                $table->id();
                $table->string('twitch_source_id')->index();
                $table->string('twitch_channel_login')->unique()->index();
                $table->timestamps();

                $table->foreign('twitch_source_id')->references('external_channel_id')->on('creator_sources')->cascadeOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('twitch_logins');
    }
};
