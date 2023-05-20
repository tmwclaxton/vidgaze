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
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('creator_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('video_id')->constrained()->cascadeOnDelete();
            $table->integer('like_count')->default('0')->unsigned();
            $table->integer('dislike_count')->default('0')->unsigned();
            $table->foreignId('parent_comment_id')->nullable()->references('id')->on('comments')->constrained()->cascadeOnDelete();
            $table->json('awards_json')->nullable();//json format
            $table->string('body' ,10000);

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
        Schema::dropIfExists('comments');
    }
};
