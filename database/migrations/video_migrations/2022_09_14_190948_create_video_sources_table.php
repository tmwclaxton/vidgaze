<?php

use App\Enums\Platform;
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
        Schema::create('video_sources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('video_id')->constrained()->cascadeOnDelete();
            $table->enum('source_name',[
                    Platform::YouTube->value,
                    Platform::Dailymotion->value,
                    Platform::Vimeo->value,
                    Platform::Rumble->value,
                    Platform::Odysee->value,
                    Platform::Twitch->value,
                    Platform::FaceBook->value
            ]);
            $table->string('external_id');
            $table->timestamps();
            $table->unique(['source_name', 'external_id']);

        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('video_sources');
    }
};
