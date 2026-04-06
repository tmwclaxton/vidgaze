<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('podcasts', function (Blueprint $table) {
            $table->string('apple_podcast_id', 64)->nullable()->unique()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('podcasts', function (Blueprint $table) {
            $table->dropUnique(['apple_podcast_id']);
            $table->dropColumn('apple_podcast_id');
        });
    }
};
