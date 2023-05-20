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
        Schema::create('video_uploads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('creator_id')->constrained()->cascadeOnDelete();
            $table->string('title')->default('')->nullable();
            $table->string('description')->default('')->nullable();
            $table->string('local_video_url')->default('')->nullable();
            $table->string('local_thumbnail_url')->default('')->nullable();
            $table->string('category')->nullable();
            $table->json('tags')->nullable();
//            $table->boolean('for_kids')->default(true);
            $table->enum('audience',['kids','mature','all'])->default('all');

            $table->enum('visibility',['public','private','unlisted','scheduled'])->default('public')->nullable();;
            $table->timestamp('scheduled_for')->default(now())->nullable();
            $table->json('platforms')->nullable();
            $table->enum('preferred_source',['youtube','dailymotion','vimeo','rumble','odysee'])->default('youtube');
            $table->string('reserved_video_slug')->default('')->nullable();
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
        Schema::dropIfExists('video_uploads');
    }
};
