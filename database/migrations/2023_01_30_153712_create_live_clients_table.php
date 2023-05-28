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
        Schema::create('live_clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('viewer_id')->references('id')->on('creators')->nullable()->constrained()->cascadeOnDelete();
            $table->string('session_id');
            $table->integer('item_id'); //video or stream or podcast id
            // type video or stream or podcast enum
            $table->enum('type', ['video', 'stream', 'podcast']);
            $table->boolean('view_counted')->default(false);
            $table->boolean('live_viewer_counted')->default(false);
            $table->timestamps();
        });
        Schema::table('live_clients', function($table) {
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
        Schema::dropIfExists('live_clients');
    }
};
