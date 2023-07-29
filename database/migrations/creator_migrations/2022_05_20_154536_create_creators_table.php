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
        Schema::create('creators', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name')->index();
            $table->string('avatar_url', 400)->nullable();
            $table->string('banner_url')->nullable();
            $table->integer('karma')->default('0')->index();
            $table->integer('subscriber_count')->default('0')->index();
            $table->integer('comment_count')->default('0')->unsigned();

            $table->json('bio')->nullable();
            $table->string('region',2)->nullable();  //ISO 3166-1 alpha-2 code
            $table->bigInteger('coins')->default('0');
            $table->string('language', 5)->nullable()->index(); //ISO 639-3:2007
            $table->foreignId('category_id')->nullable()->constrained();
            $table->string('contact_email')->nullable();
            $table->boolean('is_live')->default(false)->index()->nullable();
            $table->boolean('featured')->default(false);
            $table->timestamp('last_api_update')->nullable();
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
        Schema::dropIfExists('creators');
    }
};
