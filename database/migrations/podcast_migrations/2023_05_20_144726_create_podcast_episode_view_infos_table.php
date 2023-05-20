<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('podcast_episode_view_infos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('viewer_id')->references('id')->on('creators')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('podcast_id')->references('id')->on('podcasts')->constrained()->cascadeOnDelete();
            //1 = yes , 0 = disliked, null = neither
            $table->enum('liked',['like','dislike'])->nullable(); //use enum
            $table->smallInteger('view_point')->unsigned()->nullable(); // roughly 18 hours it can track
            $table->timestamps();
        });
        // this is to make viewer_id nullable
        Schema::table('podcast_episode_view_infos', function($table)
        {
            $table->foreignId('viewer_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('podcast_episode_view_infos');
    }
};
