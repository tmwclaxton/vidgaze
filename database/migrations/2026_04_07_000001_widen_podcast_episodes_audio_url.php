<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('podcast_episodes', function (Blueprint $table) {
            $table->dropIndex(['audio_url']);
        });

        Schema::table('podcast_episodes', function (Blueprint $table) {
            $table->text('audio_url')->change();
        });
    }

    public function down(): void
    {
        Schema::table('podcast_episodes', function (Blueprint $table) {
            $table->string('audio_url')->change();
        });

        Schema::table('podcast_episodes', function (Blueprint $table) {
            $table->index('audio_url');
        });
    }
};
