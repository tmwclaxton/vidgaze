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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('thumbnail_url')->nullable();
            //$table->string('banner_url')->nullable();
            $table->string('youtube_category_id')->nullable();
            $table->string('twitch_category_id')->nullable();
            $table->string('dailymotion_category_id')->nullable();
            $table->string('vimeo_category_id')->nullable();
            $table->string('rumble_category_id')->nullable();
            $table->string('odysee_category_id')->nullable();
            $table->string('podcast_category_id')->nullable();
            $table->json('tags_json')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('categories');
    }
};
