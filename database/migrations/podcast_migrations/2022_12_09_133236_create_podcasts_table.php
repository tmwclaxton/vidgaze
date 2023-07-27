<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('podcasts', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique()->index();
            $table->foreignId('creator_id')->constrained()->cascadeOnDelete();
            $table->string('rss_url')->unique();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title')->nullable()->index();
            $table->text('description')->nullable();
            $table->string('thumbnail_url')->nullable();
            $table->enum('visibility', ['public', 'unlisted', 'private'])->default('public');

            $table->integer('like_count')->default('0')->unsigned();
            $table->integer('view_count')->default('0')->unsigned();
            $table->integer('live_viewer_count')->unsigned()->default('0')->index();
            $table->integer('impressions')->default('0')->unsigned();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('podcasts');
    }
};
