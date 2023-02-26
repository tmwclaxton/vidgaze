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
        Schema::create('playlists', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->foreignId('creator_id')->constrained()->cascadeOnDelete();
            $table->string('name',100)->index();
            $table->boolean('server_made')->default(false); //eg watch history, stops user form removing the playlist
            $table->enum('visibility', ['public', 'unlisted', 'private', 'hidden']); // (eg disliked videos)
            $table->json('list')->nullable(); //in json format
            $table->string('description', 500)->nullable();
            $table->integer('video_count')->default('0')->index();
            $table->string('recent_video_image')->nullable();

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
        Schema::dropIfExists('playlists');
    }
};
