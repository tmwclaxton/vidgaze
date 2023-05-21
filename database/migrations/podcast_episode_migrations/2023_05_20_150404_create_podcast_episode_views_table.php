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
        Schema::create('podcast_episode_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('viewer_id')->references('id')->on('creators')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('podcast_episode_id')->references('id')->on('podcast_episodes')->constrained()->cascadeOnDelete();
            $table->integer('duration')->unsigned()->nullable();
            $table->string('session_id');
            $table->timestamps();
        });
        Schema::table('podcast_episode_views', function($table)
        {
            $table->foreignId('viewer_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('podcast_episode_views');
    }
};
