<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('podcast_episodes', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique()->index(); //will be the guid in the rss feed
            $table->foreignId('podcast_id')->constrained()->cascadeOnDelete();
            $table->string('title')->index();
            $table->string('audio_url')->index();
            $table->text('description')->nullable();
            $table->integer('karma')->default('0')->index();
            $table->string('duration')->index();
            $table->timestampTz('time_published')->nullable();
            $table->json('tags')->nullable();
            $table->string('thumbnail_url');
            $table->integer('like_count')->default('0')->unsigned();
            $table->integer('comment_count')->default('0')->unsigned();
            $table->integer('view_count')->default('0')->unsigned();
            $table->enum('visibility', ['public', 'unlisted', 'private'])->default('public');
            $table->enum('audience',['kids','mature','all'])->default('all');
            $table->string('language', 3)->nullable()->index(); //ISO 639-3:2007


            //$table->json('most_relevant_comments')->nullable();//json format
            //$table->json('most_recent_comments')->nullable();//json format
            //$table->integer('dislike_count')->default('0')->unsigned();


            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('podcast_episodes');
    }
};
