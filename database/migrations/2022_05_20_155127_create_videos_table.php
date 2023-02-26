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
        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique()->index();
            $table->foreignId('creator_id')->constrained()->cascadeOnDelete();
            $table->enum('preferred_source',['YouTube','Dailymotion','Vimeo','Rumble','Odysee']); //use enum

            $table->string('title')->index();
            $table->text('description')->nullable();
            $table->integer('karma')->default('0')->index();
            $table->string('duration')->index();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->json('tags', 1200)->nullable();
            $table->timestampTz('time_uploaded')->useCurrent()->nullable();
            $table->timestampTz('time_published')->nullable();
            $table->enum('visibility', ['public', 'unlisted', 'private'])->default('public');

            $table->json('most_relevant_comments')->nullable();//json format
            $table->json('most_recent_comments')->nullable();//json format
            $table->integer('like_count')->default('0')->unsigned();
            $table->integer('dislike_count')->default('0')->unsigned();
            $table->integer('comment_count')->default('0')->unsigned();
            $table->integer('views')->default('0')->unsigned();
            $table->integer('live_viewer_count')->unsigned()->default('0')->index();
            $table->string('thumbnail_url');
            $table->string('language', 3)->nullable()->index(); //ISO 639-3:2007
            $table->enum('audience',['kids','mature','all'])->default('all');


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
        Schema::dropIfExists('videos');
    }
};
