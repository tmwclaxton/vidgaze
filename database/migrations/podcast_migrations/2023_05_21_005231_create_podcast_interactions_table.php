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
        Schema::create('podcast_interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('creator_id')->references('id')->on('creators')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('podcast_id')->references('id')->on('podcasts')->constrained()->cascadeOnDelete();
            //1 = yes , 0 = disliked, null = neither
            $table->enum('liked',['like','dislike'])->nullable(); //use enum
            // column for saying whether you reported the podcast or not
            $table->boolean('reported')->default(false);
            $table->boolean('disinterested')->default(false);
            $table->foreignId('episode_id')->references('id')->on('podcast_episodes')->nullable()->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
        Schema::table('podcast_interactions', function($table)
        {
            // this is because the viewer_id and episode_id can be null and it for some reason doesn't allow that
            $table->foreignId('viewer_id')->nullable()->change();
            $table->foreignId('episode_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('podcast_interactions');
    }
};
