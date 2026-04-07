<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        $database = DB::getDatabaseName();

        $column = DB::selectOne(
            'select COLUMN_TYPE from information_schema.COLUMNS where TABLE_SCHEMA = ? and TABLE_NAME = ? and COLUMN_NAME = ?',
            [$database, 'podcast_episodes', 'audio_url']
        );

        if ($column === null || stripos((string) $column->COLUMN_TYPE, 'text') !== false) {
            return;
        }

        $indexes = DB::select(
            'select distinct INDEX_NAME as name from information_schema.STATISTICS where TABLE_SCHEMA = ? and TABLE_NAME = ? and COLUMN_NAME = ? and INDEX_NAME <> ?',
            [$database, 'podcast_episodes', 'audio_url', 'PRIMARY']
        );

        foreach ($indexes as $index) {
            Schema::table('podcast_episodes', function (Blueprint $table) use ($index) {
                $table->dropIndex($index->name);
            });
        }

        Schema::table('podcast_episodes', function (Blueprint $table) {
            $table->text('audio_url')->change();
        });
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        Schema::table('podcast_episodes', function (Blueprint $table) {
            $table->string('audio_url')->change();
        });

        Schema::table('podcast_episodes', function (Blueprint $table) {
            $table->index('audio_url');
        });
    }
};
