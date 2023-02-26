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
        Schema::create('video_view_infos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('viewer_id')->references('id')->on('creators')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('video_id')->references('id')->on('videos')->constrained()->cascadeOnDelete();
//            $table->boolean('liked')->nullable(); //1 = yes , 0 = disliked, null = neither
            $table->enum('liked',['like','dislike'])->nullable(); //use enum

            $table->smallInteger('view_point')->unsigned()->nullable();

            $table->timestamps();
        });
        Schema::table('video_view_infos', function($table)
        {
            $table->foreignId('viewer_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('video_view_info');
    }
};
