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
        Schema::create('video_interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('viewer_id')->references('id')->on('creators')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('video_id')->references('id')->on('videos')->constrained()->cascadeOnDelete();
             //1 = yes , 0 = disliked, null = neither
            $table->enum('liked',['like','dislike'])->nullable(); //use enum
            $table->mediumInteger('view_point')->unsigned()->nullable();
            $table->boolean('reported')->default(false);
            $table->boolean('disinterested')->default(false);



            $table->timestamps();
        });
        Schema::table('video_interactions', function($table)
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
        Schema::dropIfExists('video_podcasts');
    }
};
