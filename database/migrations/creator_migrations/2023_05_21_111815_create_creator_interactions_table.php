<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('creator_interactions', function (Blueprint $table) {
             $table->id();
            $table->foreignId('viewer_id')->references('id')->on('creators')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('creator_id')->references('id')->on('creators')->constrained()->cascadeOnDelete();
            $table->boolean('subscribed')->default(false);
            $table->boolean('reported')->default(false);
            $table->boolean('disinterested')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('creator_interactions');
    }
};
