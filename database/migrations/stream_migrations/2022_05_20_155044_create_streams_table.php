<?php

use App\Enums\Audience;
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
        Schema::create('streams', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique()->index();
            $table->foreignId('creator_id')->constrained()->cascadeOnDelete();
            $table->enum('preferred_source',['youtube','twitch']);
            $table->string('title')->index();
            $table->text('description',500)->nullable();
            $table->integer('viewers')->unsigned()->default(0)->index();
            $table->string('thumbnail_url');
            $table->integer('karma')->default('0')->index();
            $table->timestamp('started_at')->useCurrent();
            $table->enum('audience', array_map(fn($audience) => $audience->value, Audience::getAll()))->default('all');
            $table->enum('visibility', array_map(fn($audience) => $audience->value, Visibility::getAll()))->default('public');
            $table->string('language', 5)->nullable()->index(); //ISO 639-3:2007
            $table->string('region', 3)->nullable()->index();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('tags', 1200)->nullable();
            $table->boolean('is_live')->default(false)->nullable();

            $table->integer('live_viewer_count')->unsigned()->default('0')->index(); //this is the vidgaze viewer count
            $table->integer('report_count')->default('0')->unsigned();
            $table->integer('comment_count')->default('0')->unsigned();
            $table->integer('impressions')->default('0')->unsigned();

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
        Schema::dropIfExists('streams');
    }
};
