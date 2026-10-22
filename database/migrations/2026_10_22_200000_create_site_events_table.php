<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Anonymous activity counter for the public stats page. One row per page view,
 * watch or search. No user id, IP address, session id or user agent is stored.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('site_events', function (Blueprint $table) {
            $table->id();
            $table->string('type', 16);
            $table->string('page', 48)->nullable();
            $table->unsignedBigInteger('video_id')->nullable();
            $table->string('term', 60)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['type', 'created_at']);
            $table->index(['video_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_events');
    }
};
