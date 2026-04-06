<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * MySQL enum columns diverged across tables; normalize to VARCHAR for app-level validation via PlatformRegistry.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();
        if ($driver !== 'mysql') {
            return;
        }

        DB::statement('ALTER TABLE creator_sources MODIFY source_name VARCHAR(64) NOT NULL');
        DB::statement('ALTER TABLE video_sources MODIFY source_name VARCHAR(64) NOT NULL');
        DB::statement('ALTER TABLE videos MODIFY preferred_source VARCHAR(64) NOT NULL');
        DB::statement('ALTER TABLE video_drafts MODIFY preferred_source VARCHAR(64) NULL');
        DB::statement('ALTER TABLE video_views MODIFY platform VARCHAR(64) NOT NULL');
        DB::statement('ALTER TABLE streams MODIFY preferred_source VARCHAR(64) NOT NULL');
        DB::statement('ALTER TABLE stream_sources MODIFY source_name VARCHAR(64) NOT NULL');
    }

    public function down(): void
    {
        $driver = DB::getDriverName();
        if ($driver !== 'mysql') {
            return;
        }

        // Best-effort revert to prior union of platform ids (no strict enum reconstruction).
        DB::statement("ALTER TABLE creator_sources MODIFY source_name VARCHAR(64) NOT NULL");
        DB::statement("ALTER TABLE video_sources MODIFY source_name VARCHAR(64) NOT NULL");
        DB::statement("ALTER TABLE videos MODIFY preferred_source VARCHAR(64) NOT NULL");
        DB::statement("ALTER TABLE video_drafts MODIFY preferred_source VARCHAR(64) NULL");
        DB::statement("ALTER TABLE video_views MODIFY platform VARCHAR(64) NOT NULL");
        DB::statement("ALTER TABLE streams MODIFY preferred_source VARCHAR(64) NOT NULL");
        DB::statement("ALTER TABLE stream_sources MODIFY source_name VARCHAR(64) NOT NULL");
    }
};
