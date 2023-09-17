<?php

use App\Enums\Audience;
use App\Enums\Platform;
use App\Enums\Visibility;
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
            $table->enum('preferred_source', Platform::getSupportedPlatforms()->toArray());
            $table->string('title')->index();
            $table->text('description')->nullable();
            $table->integer('karma')->default(0)->index();
            $table->integer('duration')->default(0)->unsigned()->index();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->json('tags')->nullable();
            $table->timestampTz('time_uploaded')->useCurrent()->nullable();
            $table->timestampTz('time_published')->nullable();
            $table->enum('visibility', array_map(fn($audience) => $audience->value, Visibility::getAll()))->default('public');
            $table->integer('like_count')->default('0')->unsigned();
            $table->integer('dislike_count')->default('0')->unsigned();
            $table->integer('comment_count')->default('0')->unsigned();
            $table->integer('report_count')->default('0')->unsigned();
            $table->integer('view_count')->default('0')->unsigned();
            $table->integer('impressions_count')->default('0')->unsigned();
            $table->integer('live_viewer_count')->unsigned()->default('0')->index();
            $table->string('thumbnail_url');
            $table->string('language', 5)->nullable()->index(); //ISO 639-3:2007
            $table->string('region', 3)->nullable()->index();
            $table->enum('audience', array_map(fn($audience) => $audience->value, Audience::getAll()))->default('all');
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
