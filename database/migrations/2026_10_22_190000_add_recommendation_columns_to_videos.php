<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Columns and indexes for the recommender:
 *  - available / health_checked_at: set by app:check-video-health so rows
 *    skip removed videos and dead thumbnails.
 *  - FULLTEXT on title for watch page suggestions (MySQL only).
 *  - (category_id, time_published) for fast "newest in category" queries.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            if (!Schema::hasColumn('videos', 'available')) {
                $table->boolean('available')->default(true)->index();
            }
            if (!Schema::hasColumn('videos', 'health_checked_at')) {
                $table->timestamp('health_checked_at')->nullable()->index();
            }
        });

        if (DB::getDriverName() === 'mysql') {
            $indexes = collect(DB::select('SHOW INDEX FROM videos'))->pluck('Key_name')->unique();
            if (!$indexes->contains('videos_title_fulltext')) {
                DB::statement('ALTER TABLE videos ADD FULLTEXT videos_title_fulltext (title)');
            }
            if (!$indexes->contains('videos_category_published_index')) {
                DB::statement('CREATE INDEX videos_category_published_index ON videos (category_id, time_published)');
            }
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE videos DROP INDEX videos_title_fulltext');
            DB::statement('DROP INDEX videos_category_published_index ON videos');
        }
        Schema::table('videos', function (Blueprint $table) {
            $table->dropColumn(['available', 'health_checked_at']);
        });
    }
};
