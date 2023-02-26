<?php

use App\Enums\Platforms;
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
        Schema::create('creator_sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('creator_id')->constrained()->cascadeOnDelete();
            $table->enum('source_name',[
                Platforms::YouTube->name,
                Platforms::Dailymotion->name,
                Platforms::Vimeo->name,
                Platforms::Rumble->name,
                Platforms::Odysee->name,
                Platforms::Twitch->name]
            );
            $table->string('external_channel_id')->index();
            $table->timestamps();
            $table->unique(['source_name', 'external_channel_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('creator_sources');
    }
};
