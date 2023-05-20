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
            $table->string('token');
            $table->integer('item_id'); //video or stream or podcast id
            // type video or stream or podcast enum
            $table->enum('type', ['video', 'stream', 'podcast']);
            $table->boolean('view_counted')->default(false);
            $table->boolean('live_viewer_counted')->default(false);
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
        Schema::dropIfExists('live_clients');
    }
};
